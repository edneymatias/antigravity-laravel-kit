---
name: pgvector-search
description: Vector search with PostgreSQL pgvector extension. Embeddings storage, similarity search, indexing. Use when implementing semantic search or RAG with Laravel.
---

# pgvector Search for Laravel

> Vector similarity search using PostgreSQL and pgvector.

## When to Use

- Semantic search
- RAG (Retrieval-Augmented Generation)
- Similar content recommendations
- Document matching

---

## 1. Installation

### PostgreSQL Extension

```sql
-- Enable pgvector extension
CREATE EXTENSION IF NOT EXISTS vector;
```

### Migration

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pgvector
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        
        // Add vector column (1536 dimensions for OpenAI embeddings)
        DB::statement('ALTER TABLE documents ADD COLUMN embedding vector(1536)');
        
        // Create index for fast similarity search
        DB::statement('CREATE INDEX documents_embedding_idx ON documents USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
    }
    
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
```

---

## 2. Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    protected $fillable = ['content', 'metadata', 'embedding'];
    
    protected $casts = [
        'metadata' => 'array',
    ];
    
    /**
     * Set embedding from array
     */
    public function setEmbeddingAttribute(array $value): void
    {
        $this->attributes['embedding'] = '[' . implode(',', $value) . ']';
    }
    
    /**
     * Get embedding as array
     */
    public function getEmbeddingAttribute($value): ?array
    {
        if (!$value) {
            return null;
        }
        
        return json_decode($value);
    }
    
    /**
     * Find similar documents
     */
    public static function similarTo(array $embedding, int $limit = 5, float $threshold = 0.7): Collection
    {
        $vector = '[' . implode(',', $embedding) . ']';
        
        return static::select('*')
            ->selectRaw('1 - (embedding <=> ?) as similarity', [$vector])
            ->whereRaw('1 - (embedding <=> ?) > ?', [$vector, $threshold])
            ->orderByRaw('embedding <=> ?', [$vector])
            ->limit($limit)
            ->get();
    }
    
    /**
     * Search by text (requires embedding service)
     */
    public static function search(string $query, int $limit = 5): Collection
    {
        $openai = app(\App\Services\OpenAIClient::class);
        $embedding = $openai->embed($query);
        
        return static::similarTo($embedding, $limit);
    }
}
```

---

## 3. Repository Pattern

```php
namespace App\Repositories;

use App\Models\Document;
use App\Services\OpenAIClient;
use Illuminate\Support\Collection;

class VectorSearchRepository
{
    public function __construct(
        private OpenAIClient $openai,
    ) {}
    
    /**
     * Store document with embedding
     */
    public function store(string $content, array $metadata = []): Document
    {
        $embedding = $this->openai->embed($content);
        
        return Document::create([
            'content' => $content,
            'metadata' => $metadata,
            'embedding' => $embedding,
        ]);
    }
    
    /**
     * Bulk store documents
     */
    public function storeMany(array $documents): void
    {
        $contents = array_column($documents, 'content');
        $embeddings = $this->openai->embeddings($contents);
        
        foreach ($documents as $i => $doc) {
            Document::create([
                'content' => $doc['content'],
                'metadata' => $doc['metadata'] ?? [],
                'embedding' => $embeddings[$i]['embedding'],
            ]);
        }
    }
    
    /**
     * Semantic search
     */
    public function search(
        string $query,
        int $limit = 5,
        array $filters = [],
    ): Collection {
        $embedding = $this->openai->embed($query);
        $vector = '[' . implode(',', $embedding) . ']';
        
        $query = Document::select('*')
            ->selectRaw('1 - (embedding <=> ?) as similarity', [$vector])
            ->orderByRaw('embedding <=> ?', [$vector])
            ->limit($limit);
        
        // Apply metadata filters
        foreach ($filters as $key => $value) {
            $query->whereRaw("metadata->>? = ?", [$key, $value]);
        }
        
        return $query->get();
    }
    
    /**
     * Hybrid search (semantic + keyword)
     */
    public function hybridSearch(
        string $query,
        int $limit = 5,
        float $semanticWeight = 0.7,
    ): Collection {
        $embedding = $this->openai->embed($query);
        $vector = '[' . implode(',', $embedding) . ']';
        
        // Combine semantic similarity with text search
        return Document::select('*')
            ->selectRaw(
                '(? * (1 - (embedding <=> ?))) + (? * ts_rank(to_tsvector(content), plainto_tsquery(?))) as score',
                [$semanticWeight, $vector, 1 - $semanticWeight, $query]
            )
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }
}
```

---

## 4. Distance Operators

| Operator | Name | Use Case |
|----------|------|----------|
| `<=>` | Cosine distance | Most common, normalized |
| `<->` | L2 distance | Euclidean distance |
| `<#>` | Inner product | Dot product |

```php
// Cosine similarity (1 - cosine distance)
->selectRaw('1 - (embedding <=> ?) as similarity', [$vector])

// L2 distance (lower is more similar)
->orderByRaw('embedding <-> ?', [$vector])
```

---

## 5. Indexing Strategies

### IVFFlat (Approximate, Fast)

```sql
-- Good for large datasets
CREATE INDEX ON documents 
USING ivfflat (embedding vector_cosine_ops) 
WITH (lists = 100);
```

### HNSW (More Accurate, More Memory)

```sql
-- Better recall, slower build
CREATE INDEX ON documents 
USING hnsw (embedding vector_cosine_ops)
WITH (m = 16, ef_construction = 64);
```

### Index Selection

| Dataset Size | Index Type | Lists/M |
|--------------|-----------|---------|
| < 100K | IVFFlat | 100 |
| 100K - 1M | IVFFlat | 1000 |
| > 1M | HNSW | m=16 |

---

## 6. RAG Integration

```php
class RAGService
{
    public function __construct(
        private VectorSearchRepository $vectorSearch,
        private OpenAIClient $openai,
    ) {}
    
    public function query(string $question): string
    {
        // 1. Retrieve relevant documents
        $documents = $this->vectorSearch->search($question, limit: 5);
        
        // 2. Build context
        $context = $documents
            ->pluck('content')
            ->join("\n\n---\n\n");
        
        // 3. Generate answer
        return $this->openai->prompt(
            prompt: "Based on this context:\n\n{$context}\n\nAnswer: {$question}",
            systemPrompt: 'Answer based only on the provided context. Say "I don\'t know" if the answer is not in the context.',
        );
    }
}
```

---

## 7. Performance Tips

### Batch Embeddings

```php
// Bad: N API calls
foreach ($texts as $text) {
    $embedding = $openai->embed($text);
}

// Good: 1 API call
$embeddings = $openai->embeddings($texts);
```

### Use Approximate Search

```php
// Set probes for IVFFlat (higher = more accurate, slower)
DB::statement('SET ivfflat.probes = 10');
```

---

> **Remember:** Always create an index on the vector column. Use IVFFlat for most cases, HNSW for higher accuracy needs.
