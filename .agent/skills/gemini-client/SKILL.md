---
name: gemini-client
description: Integrate Google Gemini API with Laravel. HTTP client, message format, error handling. Use when calling Gemini models from Laravel applications.
---

# Gemini Client for Laravel

> Call Google Gemini APIs from Laravel using native HTTP client.

## When to Use

- Gemini chat completions (Gemini 1.5 Pro, Gemini 1.5 Flash)
- Long-context conversations (1M+ tokens)
- Multimodal tasks (text + images)
- Alternative to OpenAI/Anthropic

---

## 1. Configuration

### Environment Variables

```env
GEMINI_API_KEY=AIza...
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
```

### Config File

```php
// config/services.php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    'timeout' => 60,
],
```

---

## 2. Service Class

```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class GeminiClient
{
    private PendingRequest $http;
    private string $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        
        $this->http = Http::baseUrl(config('services.gemini.base_url'))
            ->timeout(config('services.gemini.timeout', 60))
            ->retry(3, 100, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\RequestException
                    && $exception->response?->status() === 429;
            });
    }
    
    /**
     * Generate content (chat completion)
     */
    public function generateContent(
        array $contents,
        string $model = 'gemini-1.5-flash',
        ?string $systemInstruction = null,
        float $temperature = 1.0,
        ?int $maxOutputTokens = null,
    ): array {
        $payload = [
            'contents' => $this->formatContents($contents),
            'generationConfig' => [
                'temperature' => $temperature,
            ],
        ];
        
        if ($systemInstruction) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemInstruction]],
            ];
        }
        
        if ($maxOutputTokens) {
            $payload['generationConfig']['maxOutputTokens'] = $maxOutputTokens;
        }
        
        $url = "/models/{$model}:generateContent?key={$this->apiKey}";
        $response = $this->http->post($url, $payload);
        
        $this->handleErrors($response);
        
        return $response->json();
    }
    
    /**
     * Simple prompt helper
     */
    public function prompt(
        string $prompt,
        string $model = 'gemini-1.5-flash',
        ?string $systemPrompt = null,
    ): string {
        $contents = [
            ['role' => 'user', 'content' => $prompt],
        ];
        
        $response = $this->generateContent(
            contents: $contents,
            model: $model,
            systemInstruction: $systemPrompt,
        );
        
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }
    
    /**
     * Format contents for Gemini API
     */
    private function formatContents(array $contents): array
    {
        $formatted = [];
        
        foreach ($contents as $content) {
            $formatted[] = [
                'role' => $content['role'] === 'assistant' ? 'model' : $content['role'],
                'parts' => [
                    ['text' => $content['content']],
                ],
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Generate embeddings
     */
    public function embed(
        string $text,
        string $model = 'text-embedding-004',
    ): array {
        $url = "/models/{$model}:embedContent?key={$this->apiKey}";
        
        $response = $this->http->post($url, [
            'model' => "models/{$model}",
            'content' => [
                'parts' => [['text' => $text]],
            ],
        ]);
        
        $this->handleErrors($response);
        
        return $response->json()['embedding']['values'];
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

## 3. Usage Examples

### Basic Chat

```php
$gemini = app(GeminiClient::class);

$response = $gemini->prompt(
    prompt: 'Explain Laravel queues in 3 sentences.',
    systemPrompt: 'You are a Laravel expert. Be concise.'
);
```

### With Conversation History

```php
$contents = [
    ['role' => 'user', 'content' => 'What is Laravel?'],
    ['role' => 'assistant', 'content' => 'Laravel is a PHP web framework...'],
    ['role' => 'user', 'content' => 'How do I install it?'],
];

$response = $gemini->generateContent(
    contents: $contents,
    systemInstruction: 'You are a helpful Laravel assistant.',
);

$answer = $response['candidates'][0]['content']['parts'][0]['text'];
```

### Generate Embeddings

```php
$embedding = $gemini->embed('Laravel is a PHP framework');
// Returns: [0.0023, -0.0145, 0.0312, ...]
```

---

## 4. Models Reference

| Model | Context | Speed | Use Case |
|-------|---------|-------|----------|
| `gemini-1.5-flash` | 1M | Fast | General use, cost-effective |
| `gemini-1.5-pro` | 2M | Medium | Complex reasoning |
| `gemini-1.0-pro` | 32K | Fast | Legacy, simple tasks |
| `text-embedding-004` | - | Fast | Embeddings |

---

## 5. Key Differences from OpenAI

| Feature | OpenAI | Gemini |
|---------|--------|--------|
| Auth | Header token | Query param `key=` |
| Role names | `assistant` | `model` |
| Response format | `choices[0].message.content` | `candidates[0].content.parts[0].text` |
| System prompt | In messages | `systemInstruction` |
| Content format | String | `parts` array |

---

## 6. Service Provider

```php
namespace App\Providers;

use App\Services\GeminiClient;
use Illuminate\Support\ServiceProvider;

class GeminiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeminiClient::class, function () {
            return new GeminiClient();
        });
    }
}
```

---

## 7. Multimodal (Image + Text)

```php
public function promptWithImage(string $prompt, string $imagePath): string
{
    $imageData = base64_encode(file_get_contents($imagePath));
    $mimeType = mime_content_type($imagePath);
    
    $contents = [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
                [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => $imageData,
                    ],
                ],
            ],
        ],
    ];
    
    // ... send to API
}
```

---

> **Remember:** Gemini uses `model` instead of `assistant` for roles. API key is passed as query parameter, not header.
