---
name: anthropic-client
description: Integrate Anthropic Claude API with Laravel. HTTP client, message format, error handling. Use when calling Claude models from Laravel applications.
---

# Anthropic Client for Laravel

> Call Anthropic Claude APIs from Laravel using native HTTP client.

## When to Use

- Claude chat completions (Claude 3.5 Sonnet, Claude 3 Opus)
- Long-context conversations (200K tokens)
- Complex reasoning tasks
- Alternative to OpenAI

---

## 1. Configuration

### Environment Variables

```env
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_BASE_URL=https://api.anthropic.com
```

### Config File

```php
// config/services.php
'anthropic' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
    'timeout' => 60,
    'version' => '2023-06-01',
],
```

---

## 2. Service Class

```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class AnthropicClient
{
    private PendingRequest $http;
    
    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.anthropic.base_url'))
            ->timeout(config('services.anthropic.timeout', 60))
            ->withHeaders([
                'x-api-key' => config('services.anthropic.api_key'),
                'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
                'content-type' => 'application/json',
            ])
            ->retry(3, 100, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\RequestException
                    && $exception->response?->status() === 429;
            });
    }
    
    /**
     * Create a message (chat completion)
     */
    public function message(
        array $messages,
        string $model = 'claude-3-5-sonnet-20241022',
        int $maxTokens = 4096,
        ?string $system = null,
        float $temperature = 1.0,
    ): array {
        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $this->formatMessages($messages),
            'temperature' => $temperature,
        ];
        
        if ($system) {
            $payload['system'] = $system;
        }
        
        $response = $this->http->post('/v1/messages', $payload);
        
        $this->handleErrors($response);
        
        return $response->json();
    }
    
    /**
     * Simple prompt helper
     */
    public function prompt(
        string $prompt,
        string $model = 'claude-3-5-sonnet-20241022',
        ?string $systemPrompt = null,
    ): string {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];
        
        $response = $this->message(
            messages: $messages,
            model: $model,
            system: $systemPrompt,
        );
        
        return $response['content'][0]['text'];
    }
    
    /**
     * Format messages for Anthropic API
     * Anthropic requires alternating user/assistant messages
     */
    private function formatMessages(array $messages): array
    {
        $formatted = [];
        
        foreach ($messages as $message) {
            // Skip system messages (handled separately)
            if ($message['role'] === 'system') {
                continue;
            }
            
            $formatted[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Handle API errors
     */
    private function handleErrors(Response $response): void
    {
        if ($response->successful()) {
            return;
        }
        
        $error = $response->json('error');
        
        match ($response->status()) {
            401 => throw new \Exception('Invalid API key'),
            429 => throw new \Exception('Rate limit exceeded'),
            400 => throw new \Exception('Bad request: ' . ($error['message'] ?? '')),
            500, 502, 503 => throw new \Exception('Anthropic server error'),
            default => throw new \Exception('Anthropic error: ' . ($error['message'] ?? 'Unknown')),
        };
    }
}
```

---

## 3. Usage Examples

### Basic Chat

```php
$claude = app(AnthropicClient::class);

$response = $claude->prompt(
    prompt: 'Explain Laravel queues in 3 sentences.',
    systemPrompt: 'You are a Laravel expert. Be concise.'
);
```

### With Conversation History

```php
$messages = [
    ['role' => 'user', 'content' => 'What is Laravel?'],
    ['role' => 'assistant', 'content' => 'Laravel is a PHP web framework...'],
    ['role' => 'user', 'content' => 'How do I install it?'],
];

$response = $claude->message(
    messages: $messages,
    system: 'You are a helpful Laravel assistant.',
);

$answer = $response['content'][0]['text'];
```

### Long Context

```php
// Claude 3 supports 200K tokens
$longDocument = file_get_contents('large_document.txt');

$response = $claude->prompt(
    prompt: "Summarize this document:\n\n{$longDocument}",
    model: 'claude-3-5-sonnet-20241022',
);
```

---

## 4. Models Reference

| Model | Context | Speed | Use Case |
|-------|---------|-------|----------|
| `claude-3-5-sonnet-20241022` | 200K | Fast | Best balance |
| `claude-3-opus-20240229` | 200K | Slow | Complex tasks |
| `claude-3-haiku-20240307` | 200K | Fastest | Simple tasks, cheap |

---

## 5. Key Differences from OpenAI

| Feature | OpenAI | Anthropic |
|---------|--------|-----------|
| Auth header | `Authorization: Bearer` | `x-api-key` |
| System prompt | In messages array | Separate `system` param |
| Response format | `choices[0].message.content` | `content[0].text` |
| Max tokens | Optional | Required |

---

## 6. Service Provider

```php
namespace App\Providers;

use App\Services\AnthropicClient;
use Illuminate\Support\ServiceProvider;

class AnthropicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AnthropicClient::class, function () {
            return new AnthropicClient();
        });
    }
}
```

---

> **Remember:** Claude requires `max_tokens` to be set. System prompts are passed separately, not in the messages array.
