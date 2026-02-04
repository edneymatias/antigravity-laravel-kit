---
name: openai-client
description: Integrate OpenAI API with Laravel. HTTP client, error handling, rate limiting. Use when calling GPT models from Laravel applications.
---

# OpenAI Client for Laravel

> Call OpenAI APIs from Laravel using native HTTP client.

## When to Use

- Chat completions (GPT-4, GPT-3.5)
- Text embeddings
- Image generation (DALL-E)
- Audio transcription (Whisper)

---

## 1. Configuration

### Environment Variables

```env
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...  # Optional
OPENAI_BASE_URL=https://api.openai.com/v1  # Optional, for proxies
```

### Config File

```php
// config/services.php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'timeout' => 30,
],
```

---

## 2. Service Class

```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class OpenAIClient
{
    private PendingRequest $http;
    
    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.openai.base_url'))
            ->withToken(config('services.openai.api_key'))
            ->timeout(config('services.openai.timeout', 30))
            ->withHeaders([
                'OpenAI-Organization' => config('services.openai.organization'),
            ])
            ->retry(3, 100, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\RequestException
                    && $exception->response?->status() === 429;
            });
    }
    
    /**
     * Chat completion
     */
    public function chat(
        array $messages,
        string $model = 'gpt-4-turbo',
        float $temperature = 0.7,
        ?int $maxTokens = null,
    ): array {
        $response = $this->http->post('/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);
        
        $this->handleErrors($response);
        
        return $response->json();
    }
    
    /**
     * Simple prompt helper
     */
    public function prompt(
        string $prompt,
        string $model = 'gpt-4-turbo',
        ?string $systemPrompt = null,
    ): string {
        $messages = [];
        
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        
        $messages[] = ['role' => 'user', 'content' => $prompt];
        
        $response = $this->chat($messages, $model);
        
        return $response['choices'][0]['message']['content'];
    }
    
    /**
     * Create embeddings
     */
    public function embeddings(
        string|array $input,
        string $model = 'text-embedding-3-small',
    ): array {
        $response = $this->http->post('/embeddings', [
            'model' => $model,
            'input' => $input,
        ]);
        
        $this->handleErrors($response);
        
        return $response->json()['data'];
    }
    
    /**
     * Single embedding helper
     */
    public function embed(string $text, string $model = 'text-embedding-3-small'): array
    {
        $embeddings = $this->embeddings($text, $model);
        return $embeddings[0]['embedding'];
    }
    
    /**
     * Handle API errors
     */
    private function handleErrors(Response $response): void
    {
        if ($response->successful()) {
            return;
        }
        
        $response->throw();
    }
}
```

---

## 3. Service Provider

```php
namespace App\Providers;

use App\Services\OpenAIClient;
use Illuminate\Support\ServiceProvider;

class OpenAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenAIClient::class, function () {
            return new OpenAIClient();
        });
    }
}
```

---

## 4. Usage Examples

### Basic Chat

```php
$openai = app(OpenAIClient::class);

$response = $openai->prompt(
    prompt: 'Explain Laravel queues in 3 sentences.',
    systemPrompt: 'You are a Laravel expert. Be concise.'
);
```

### With Conversation History

```php
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'What is Laravel?'],
    ['role' => 'assistant', 'content' => 'Laravel is a PHP web framework...'],
    ['role' => 'user', 'content' => 'How do I install it?'],
];

$response = $openai->chat($messages);
$answer = $response['choices'][0]['message']['content'];
```

### Generate Embeddings

```php
$embedding = $openai->embed('Laravel is a PHP framework');
// Returns: [0.0023, -0.0145, 0.0312, ...]
```

---

## 5. Queue Integration

### Job for Async Processing

```php
class ProcessWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public Document $document,
    ) {}
    
    public function handle(OpenAIClient $openai): void
    {
        $summary = $openai->prompt(
            prompt: "Summarize: {$this->document->content}",
            systemPrompt: 'Create a brief summary.',
        );
        
        $this->document->update(['summary' => $summary]);
    }
    
    public int $tries = 3;
    public int $backoff = 60;
}
```

---

## 6. Rate Limiting

```php
use Illuminate\Support\Facades\RateLimiter;

class OpenAIClient
{
    public function prompt(string $prompt): string
    {
        $key = 'openai-api';
        
        if (RateLimiter::tooManyAttempts($key, 60)) {
            throw new \Exception('Rate limit: try again in ' . 
                RateLimiter::availableIn($key) . ' seconds');
        }
        
        RateLimiter::hit($key);
        
        // ... make request
    }
}
```

---

## 7. Models Reference

| Model | Context | Use Case |
|-------|---------|----------|
| `gpt-4-turbo` | 128K | Complex reasoning |
| `gpt-4o` | 128K | Fast, multimodal |
| `gpt-4o-mini` | 128K | Cost-effective |
| `gpt-3.5-turbo` | 16K | Simple tasks |
| `text-embedding-3-small` | 8K | Embeddings (cheap) |
| `text-embedding-3-large` | 8K | Embeddings (quality) |

---

## Commands Reference

```bash
# Add to config/services.php
# Add to .env
# Register provider in bootstrap/providers.php (L11) or config/app.php
```

---

> **Remember:** Always handle rate limits and errors gracefully. Use queues for non-blocking AI calls.
