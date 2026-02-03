---
name: prompt-templates
description: Create and manage prompt templates for LLM applications. Variable interpolation, few-shot examples, system prompts. Use when building reusable prompts.
---

# Prompt Templates for Laravel

> Build reusable, maintainable prompt templates.

## When to Use

- Creating reusable prompts
- Few-shot learning examples
- System prompt design
- Prompt versioning
- A/B testing prompts

---

## 1. Basic Template Class

```php
namespace App\Services\Prompts;

use Illuminate\Support\Str;

class PromptTemplate
{
    public function __construct(
        private string $template,
        private array $variables = [],
    ) {}
    
    /**
     * Render template with variables
     */
    public function render(array $values): string
    {
        $prompt = $this->template;
        
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }
            $prompt = str_replace("{{$key}}", $value, $prompt);
        }
        
        // Validate all variables are filled
        if (preg_match('/\{(\w+)\}/', $prompt, $matches)) {
            throw new \InvalidArgumentException("Missing variable: {$matches[1]}");
        }
        
        return trim($prompt);
    }
    
    /**
     * Get required variables
     */
    public function getVariables(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->template, $matches);
        return array_unique($matches[1]);
    }
}

// Usage
$template = new PromptTemplate(
    'Summarize this {document_type} in {style} style:\n\n{content}'
);

$prompt = $template->render([
    'document_type' => 'article',
    'style' => 'professional',
    'content' => $articleContent,
]);
```

---

## 2. Prompt Registry

```php
namespace App\Services\Prompts;

class PromptRegistry
{
    private static array $prompts = [];
    
    /**
     * Register a prompt template
     */
    public static function register(string $name, string $template): void
    {
        self::$prompts[$name] = new PromptTemplate($template);
    }
    
    /**
     * Get and render a prompt
     */
    public static function get(string $name, array $values = []): string
    {
        if (!isset(self::$prompts[$name])) {
            throw new \InvalidArgumentException("Prompt not found: {$name}");
        }
        
        return self::$prompts[$name]->render($values);
    }
    
    /**
     * Load prompts from config
     */
    public static function loadFromConfig(): void
    {
        foreach (config('prompts', []) as $name => $template) {
            self::register($name, $template);
        }
    }
}

// config/prompts.php
return [
    'summarize' => 'Summarize the following text in {length} words:\n\n{text}',
    'translate' => 'Translate to {language}:\n\n{text}',
    'extract' => 'Extract {entity_type} from:\n\n{text}\n\nRespond in JSON format.',
];

// Usage
$summary = PromptRegistry::get('summarize', [
    'length' => 100,
    'text' => $document,
]);
```

---

## 3. Blade-Style Templates

```php
namespace App\Services\Prompts;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class BladePromptTemplate
{
    /**
     * Render prompt using Blade
     */
    public static function render(string $viewName, array $data = []): string
    {
        return view("prompts.{$viewName}", $data)->render();
    }
}

// resources/views/prompts/summarize.blade.php
You are a {{$role ?? 'helpful assistant'}}.

@if(isset($context))
Context:
{{$context}}
@endif

Task: Summarize the following {{$documentType}}:

{{$content}}

@if(isset($format))
Format: {{$format}}
@endif

// Usage
$prompt = BladePromptTemplate::render('summarize', [
    'role' => 'technical writer',
    'documentType' => 'research paper',
    'content' => $paper,
    'format' => '3 bullet points',
]);
```

---

## 4. Few-Shot Examples

```php
namespace App\Services\Prompts;

class FewShotTemplate
{
    public function __construct(
        private string $instruction,
        private array $examples = [],
        private string $inputLabel = 'Input',
        private string $outputLabel = 'Output',
    ) {}
    
    /**
     * Add an example
     */
    public function addExample(string $input, string $output): self
    {
        $this->examples[] = compact('input', 'output');
        return $this;
    }
    
    /**
     * Render with input
     */
    public function render(string $input): string
    {
        $prompt = $this->instruction . "\n\n";
        
        // Add examples
        foreach ($this->examples as $example) {
            $prompt .= "{$this->inputLabel}: {$example['input']}\n";
            $prompt .= "{$this->outputLabel}: {$example['output']}\n\n";
        }
        
        // Add current input
        $prompt .= "{$this->inputLabel}: {$input}\n";
        $prompt .= "{$this->outputLabel}:";
        
        return $prompt;
    }
}

// Usage
$classifier = new FewShotTemplate(
    instruction: 'Classify the sentiment of the following text as positive, negative, or neutral.'
);

$classifier
    ->addExample('I love this product!', 'positive')
    ->addExample('This is terrible.', 'negative')
    ->addExample('The package arrived today.', 'neutral');

$prompt = $classifier->render('Great service, will buy again!');
```

---

## 5. System Prompt Builder

```php
namespace App\Services\Prompts;

class SystemPromptBuilder
{
    private string $role = '';
    private array $traits = [];
    private array $rules = [];
    private string $format = '';
    
    public function role(string $role): self
    {
        $this->role = $role;
        return $this;
    }
    
    public function trait(string $trait): self
    {
        $this->traits[] = $trait;
        return $this;
    }
    
    public function rule(string $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }
    
    public function outputFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }
    
    public function build(): string
    {
        $parts = [];
        
        if ($this->role) {
            $parts[] = "You are {$this->role}.";
        }
        
        if ($this->traits) {
            $parts[] = "You are " . implode(', ', $this->traits) . ".";
        }
        
        if ($this->rules) {
            $parts[] = "\nRules:\n" . implode("\n", array_map(
                fn($r) => "- {$r}",
                $this->rules
            ));
        }
        
        if ($this->format) {
            $parts[] = "\nRespond in {$this->format} format.";
        }
        
        return implode("\n", $parts);
    }
}

// Usage
$systemPrompt = (new SystemPromptBuilder())
    ->role('a Laravel expert')
    ->trait('concise')
    ->trait('practical')
    ->rule('Always provide code examples')
    ->rule('Explain trade-offs')
    ->outputFormat('markdown')
    ->build();
```

---

## 6. Prompt Chain

```php
namespace App\Services\Prompts;

class PromptChain
{
    private array $steps = [];
    
    public function add(string $name, PromptTemplate $template): self
    {
        $this->steps[] = compact('name', 'template');
        return $this;
    }
    
    /**
     * Execute chain with LLM
     */
    public function execute(array $initialValues, callable $llmCall): array
    {
        $context = $initialValues;
        $results = [];
        
        foreach ($this->steps as $step) {
            $prompt = $step['template']->render($context);
            $response = $llmCall($prompt);
            
            $results[$step['name']] = $response;
            $context[$step['name'] . '_result'] = $response;
        }
        
        return $results;
    }
}

// Usage
$chain = new PromptChain();

$chain
    ->add('extract', new PromptTemplate('Extract key points from:\n\n{document}'))
    ->add('summarize', new PromptTemplate('Summarize these points:\n\n{extract_result}'))
    ->add('title', new PromptTemplate('Create a title for:\n\n{summarize_result}'));

$results = $chain->execute(
    ['document' => $longDocument],
    fn($prompt) => $openai->prompt($prompt)
);
```

---

## 7. Stored Prompts (Database)

```php
// Migration
Schema::create('prompts', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->text('template');
    $table->string('version')->default('1.0');
    $table->json('metadata')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});

// Model
class Prompt extends Model
{
    public function render(array $values): string
    {
        $template = new PromptTemplate($this->template);
        return $template->render($values);
    }
    
    public static function get(string $name): string
    {
        $prompt = static::where('name', $name)->where('active', true)->firstOrFail();
        return $prompt->template;
    }
}
```

---

## 8. Best Practices

### Do's

| Practice | Reason |
|----------|--------|
| Version your prompts | Track changes |
| Use clear variable names | Maintainability |
| Include output format | Consistent responses |
| Test with edge cases | Reliability |

### Don'ts

| Anti-Pattern | Problem |
|--------------|---------|
| Hardcoded prompts | Hard to update |
| Vague instructions | Inconsistent outputs |
| No examples | Poor quality |
| Ignoring token limits | Truncation |

---

> **Remember:** Prompts are code. Version them, test them, and document them.
