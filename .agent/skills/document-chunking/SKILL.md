---
name: document-chunking
description: Strategies for splitting documents into chunks for embeddings and RAG. Chunk sizing, overlap, semantic splitting. Use when preparing documents for vector search.
---

# Document Chunking for Laravel

> Split documents into optimal chunks for embeddings and RAG.

## When to Use

- Preparing documents for vector embeddings
- Building RAG systems
- Processing large text files
- Indexing for semantic search

---

## 1. Chunking Strategies

| Strategy | Best For | Pros | Cons |
|----------|----------|------|------|
| Fixed size | Simple content | Fast, predictable | May break mid-sentence |
| Sentence | Articles | Semantic boundaries | Variable sizes |
| Paragraph | Documents | Natural breaks | May be too large |
| Recursive | Mixed content | Adaptive | More complex |
| Semantic | Technical | Preserves structure | Needs headers |

---

## 2. Fixed Size Chunker

```php
namespace App\Services\Chunking;

class FixedSizeChunker
{
    public function __construct(
        private int $chunkSize = 500,
        private int $overlap = 50,
    ) {}
    
    /**
     * Split text into fixed-size chunks
     */
    public function chunk(string $text): array
    {
        $chunks = [];
        $length = mb_strlen($text);
        $start = 0;
        
        while ($start < $length) {
            $chunk = mb_substr($text, $start, $this->chunkSize);
            $chunks[] = trim($chunk);
            $start += $this->chunkSize - $this->overlap;
        }
        
        return array_filter($chunks);
    }
}
```

---

## 3. Sentence Chunker

```php
namespace App\Services\Chunking;

class SentenceChunker
{
    public function __construct(
        private int $maxChunkSize = 500,
        private int $minChunkSize = 100,
    ) {}
    
    /**
     * Split text by sentences, grouping to target size
     */
    public function chunk(string $text): array
    {
        $sentences = $this->splitSentences($text);
        $chunks = [];
        $currentChunk = [];
        $currentSize = 0;
        
        foreach ($sentences as $sentence) {
            $sentenceSize = mb_strlen($sentence);
            
            // If adding this sentence exceeds max, save current chunk
            if ($currentSize + $sentenceSize > $this->maxChunkSize && $currentChunk) {
                $chunks[] = implode(' ', $currentChunk);
                $currentChunk = [];
                $currentSize = 0;
            }
            
            $currentChunk[] = $sentence;
            $currentSize += $sentenceSize;
        }
        
        // Don't forget the last chunk
        if ($currentChunk) {
            $chunks[] = implode(' ', $currentChunk);
        }
        
        return $chunks;
    }
    
    /**
     * Split text into sentences
     */
    private function splitSentences(string $text): array
    {
        // Split on sentence boundaries
        $pattern = '/(?<=[.!?])\s+(?=[A-Z])/';
        $sentences = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);
        
        return array_map('trim', $sentences);
    }
}
```

---

## 4. Paragraph Chunker

```php
namespace App\Services\Chunking;

class ParagraphChunker
{
    public function __construct(
        private int $maxChunkSize = 1000,
    ) {}
    
    /**
     * Split text by paragraphs
     */
    public function chunk(string $text): array
    {
        // Split on double newlines
        $paragraphs = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $chunks = [];
        $currentChunk = [];
        $currentSize = 0;
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            $paragraphSize = mb_strlen($paragraph);
            
            // If paragraph alone is too big, use sentence chunker
            if ($paragraphSize > $this->maxChunkSize) {
                if ($currentChunk) {
                    $chunks[] = implode("\n\n", $currentChunk);
                    $currentChunk = [];
                    $currentSize = 0;
                }
                
                $sentenceChunker = new SentenceChunker($this->maxChunkSize);
                $chunks = array_merge($chunks, $sentenceChunker->chunk($paragraph));
                continue;
            }
            
            // Combine small paragraphs
            if ($currentSize + $paragraphSize > $this->maxChunkSize && $currentChunk) {
                $chunks[] = implode("\n\n", $currentChunk);
                $currentChunk = [];
                $currentSize = 0;
            }
            
            $currentChunk[] = $paragraph;
            $currentSize += $paragraphSize;
        }
        
        if ($currentChunk) {
            $chunks[] = implode("\n\n", $currentChunk);
        }
        
        return $chunks;
    }
}
```

---

## 5. Recursive Character Splitter

```php
namespace App\Services\Chunking;

class RecursiveChunker
{
    private array $separators = ["\n\n", "\n", ". ", " ", ""];
    
    public function __construct(
        private int $chunkSize = 500,
        private int $chunkOverlap = 50,
    ) {}
    
    /**
     * Recursively split text using hierarchical separators
     */
    public function chunk(string $text): array
    {
        return $this->splitText($text, $this->separators);
    }
    
    private function splitText(string $text, array $separators): array
    {
        if (empty($separators)) {
            // Final fallback: character split
            return $this->charSplit($text);
        }
        
        $separator = array_shift($separators);
        
        if ($separator === '') {
            return $this->charSplit($text);
        }
        
        $parts = explode($separator, $text);
        
        $chunks = [];
        $currentChunk = [];
        $currentSize = 0;
        
        foreach ($parts as $part) {
            $partSize = mb_strlen($part);
            
            // If single part is too large, split recursively
            if ($partSize > $this->chunkSize) {
                // Save current chunk first
                if ($currentChunk) {
                    $chunks[] = implode($separator, $currentChunk);
                    $currentChunk = [];
                    $currentSize = 0;
                }
                // Recursively split large part
                $chunks = array_merge($chunks, $this->splitText($part, $separators));
                continue;
            }
            
            // If adding part exceeds size, save and start new
            if ($currentSize + $partSize + mb_strlen($separator) > $this->chunkSize && $currentChunk) {
                $chunks[] = implode($separator, $currentChunk);
                
                // Keep overlap
                $overlapChunks = $this->getOverlapParts($currentChunk, $separator);
                $currentChunk = $overlapChunks;
                $currentSize = mb_strlen(implode($separator, $currentChunk));
            }
            
            $currentChunk[] = $part;
            $currentSize += $partSize + mb_strlen($separator);
        }
        
        if ($currentChunk) {
            $chunks[] = implode($separator, $currentChunk);
        }
        
        return array_map('trim', array_filter($chunks));
    }
    
    private function charSplit(string $text): array
    {
        $chunks = [];
        $start = 0;
        $length = mb_strlen($text);
        
        while ($start < $length) {
            $chunks[] = mb_substr($text, $start, $this->chunkSize);
            $start += $this->chunkSize - $this->chunkOverlap;
        }
        
        return $chunks;
    }
    
    private function getOverlapParts(array $parts, string $separator): array
    {
        $result = [];
        $size = 0;
        
        foreach (array_reverse($parts) as $part) {
            if ($size + mb_strlen($part) > $this->chunkOverlap) {
                break;
            }
            array_unshift($result, $part);
            $size += mb_strlen($part) + mb_strlen($separator);
        }
        
        return $result;
    }
}
```

---

## 6. Markdown Header Splitter

```php
namespace App\Services\Chunking;

class MarkdownChunker
{
    /**
     * Split markdown by headers, preserving hierarchy
     */
    public function chunk(string $markdown): array
    {
        $chunks = [];
        $currentChunk = [];
        $currentHeaders = [];
        
        $lines = explode("\n", $markdown);
        
        foreach ($lines as $line) {
            // Check if line is a header
            if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $matches)) {
                // Save previous chunk
                if ($currentChunk) {
                    $chunks[] = $this->formatChunk($currentHeaders, $currentChunk);
                }
                
                $level = strlen($matches[1]);
                $title = $matches[2];
                
                // Update header hierarchy
                $currentHeaders[$level] = $title;
                // Clear lower level headers
                foreach ($currentHeaders as $l => $h) {
                    if ($l > $level) {
                        unset($currentHeaders[$l]);
                    }
                }
                
                $currentChunk = [];
            } else {
                $currentChunk[] = $line;
            }
        }
        
        // Last chunk
        if ($currentChunk) {
            $chunks[] = $this->formatChunk($currentHeaders, $currentChunk);
        }
        
        return array_filter($chunks, fn($c) => !empty(trim($c['content'])));
    }
    
    private function formatChunk(array $headers, array $lines): array
    {
        return [
            'headers' => $headers,
            'content' => trim(implode("\n", $lines)),
            'context' => implode(' > ', $headers),
        ];
    }
}
```

---

## 7. Chunk Size Guidelines

| Embedding Model | Max Tokens | Recommended Chunk |
|----------------|------------|-------------------|
| OpenAI text-embedding-3-small | 8191 | 500-1000 |
| OpenAI text-embedding-3-large | 8191 | 500-1000 |
| Voyage | 4000 | 400-800 |
| BGE | 512 | 300-500 |

### Rules of Thumb

- **Chunk size**: 500-1000 characters (or ~100-200 words)
- **Overlap**: 10-20% of chunk size
- **Too small**: Loses context
- **Too large**: Dilutes relevance

---

## 8. Usage Example

```php
class DocumentIngestionService
{
    public function __construct(
        private VectorSearchRepository $vectorSearch,
    ) {}
    
    public function ingestDocument(string $content, array $metadata = []): void
    {
        $chunker = new RecursiveChunker(chunkSize: 500, chunkOverlap: 50);
        $chunks = $chunker->chunk($content);
        
        foreach ($chunks as $index => $chunk) {
            $this->vectorSearch->store($chunk, [
                ...$metadata,
                'chunk_index' => $index,
            ]);
        }
    }
    
    public function ingestMarkdown(string $markdown, array $metadata = []): void
    {
        $chunker = new MarkdownChunker();
        $chunks = $chunker->chunk($markdown);
        
        foreach ($chunks as $index => $chunk) {
            $this->vectorSearch->store($chunk['content'], [
                ...$metadata,
                'chunk_index' => $index,
                'context' => $chunk['context'],
            ]);
        }
    }
}
```

---

> **Remember:** Overlap prevents losing context at chunk boundaries. Use markdown chunking for structured documents.
