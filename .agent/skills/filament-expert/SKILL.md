---
name: filament-expert
description: Filament 4 admin panel patterns including Resources, Forms, Tables, Widgets, Panels, and performance optimization.
---

# Filament 4 Expert

> Server-Driven UI framework for Laravel admin panels, built on Livewire, Alpine.js, and Tailwind CSS.

## Installation & Setup

```bash
# Install Filament
composer require filament/filament:"^4.0"

# Create a panel
php artisan filament:install --panels

# Create a user
php artisan make:filament-user
```

### Requirements (Filament 4)
- PHP 8.2+
- Laravel 11.28+
- Tailwind CSS 4.0+

---

## Critical v4 Changes

> ⚠️ These changes from v3 to v4 are common sources of errors.

| Change | v3 | v4 |
|--------|----|----|
| **Layout Namespace** | `Forms\Components\{Grid,Section,Fieldset}` | `Filament\Schemas\Components\...` |
| **All Actions** | `Tables\Actions\*` | Extend `Filament\Actions\Action` |
| **Icons** | String-based (`'heroicon-o-plus'`) | `Heroicon` Enum |
| **File Visibility** | Public (default) | **Private** (default) |
| **deferFilters** | Opt-in | **Default behavior** |
| **Grid/Section** | Span all columns | **Don't span by default** |

### Namespace Migration

```php
// ❌ v3 (wrong in v4)
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

// ✅ v4 (correct)
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
```

---

## Resources (CRUD)

### Creating Resources

```bash
php artisan make:filament-resource Post --generate
```

Creates:
- `app/Filament/Resources/PostResource.php`
- `app/Filament/Resources/PostResource/Pages/`

### Resource Structure

```php
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextInput;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = Heroicon::DocumentText;
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    RichEditor::make('body')
                        ->required(),
                ]),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
```

### Nested Resources (New in v4)

```php
// Resources can be deeply nested
PostResource::make()
    ->childResource(CommentResource::class);
```

---

## Form Components

### Text Inputs

```php
TextInput::make('name')
    ->required()
    ->maxLength(255)
    ->placeholder('Enter name')
    ->helperText('This will be displayed publicly');

TextInput::make('price')
    ->numeric()
    ->prefix('$')
    ->suffix('USD');

TextInput::make('password')
    ->password()
    ->revealable();
```

### Selects with Relationships

```php
Select::make('user_id')
    ->label('Author')
    ->relationship('author', 'name')
    ->searchable()
    ->preload()
    ->required();

Select::make('tags')
    ->relationship('tags', 'name')
    ->multiple()
    ->preload();
```

### New in v4: Slider & Code Editor

```php
// Slider
Slider::make('rating')
    ->min(1)
    ->max(5)
    ->step(1);

// Code Editor
CodeEditor::make('code')
    ->language('php');

// Table Repeater
TableRepeater::make('items')
    ->schema([
        TextInput::make('name'),
        TextInput::make('quantity')->numeric(),
    ]);
```

### Repeaters

```php
Repeater::make('addresses')
    ->schema([
        TextInput::make('street'),
        TextInput::make('city'),
        Select::make('country')
            ->options(Country::pluck('name', 'id')),
    ])
    ->columns(2)
    ->defaultItems(1)
    ->collapsible();
```

---

## TipTap Rich Editor (New in v4)

Replaces Trix with more features:

```php
RichEditor::make('content')
    ->toolbarButtons([
        'bold', 'italic', 'link', 'bulletList', 'orderedList',
        'h2', 'h3', 'blockquote', 'codeBlock',
    ])
    ->extraAttributes(['class' => 'prose']);
```

### Custom Blocks & Merge Tags

```php
RichEditor::make('email_template')
    ->mergeTags(['{{name}}', '{{email}}', '{{date}}']);
```

---

## Table Components

### Columns

```php
TextColumn::make('title')
    ->searchable()
    ->sortable()
    ->limit(50);

TextColumn::make('status')
    ->badge()
    ->color(fn (string $state) => match ($state) {
        'draft' => 'gray',
        'published' => 'success',
        'archived' => 'danger',
    });

ImageColumn::make('avatar')
    ->circular();

IconColumn::make('is_active')
    ->boolean();
```

### Custom Data Sources (New in v4)

Tables can use non-Eloquent data:

```php
public function table(Table $table): Table
{
    return $table
        ->query(fn () => collect($this->getApiData()))
        ->columns([...]);
}
```

### Filters

```php
SelectFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
    ]);

TernaryFilter::make('is_active');

Filter::make('created_at')
    ->form([
        DatePicker::make('from'),
        DatePicker::make('until'),
    ])
    ->query(fn (Builder $query, array $data) => $query
        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
        ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
    );
```

---

## Widgets

### Stats Widgets

```php
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->description('All time')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success'),
            Stat::make('Pending', Post::pending()->count())
                ->color('warning'),
        ];
    }
}
```

### Chart Widgets

```php
class PostsChart extends ChartWidget
{
    protected static ?string $heading = 'Posts per Month';
    
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Posts',
                    'data' => [10, 20, 30, 40, 50, 60],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}
```

---

## Multi-Panel Setup

```php
// app/Providers/Filament/AdminPanelProvider.php
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors(['primary' => Color::Amber])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets');
    }
}
```

---

## Client-Side JS Helpers (New in v4)

Reduce network requests with JS control:

```php
// Hide field based on JS condition
TextInput::make('discount_code')
    ->hiddenJs('data.payment_method !== "credit_card"');

// JS callback after state update
Select::make('country')
    ->afterStateUpdatedJs('console.log("Country changed:", state)');
```

---

## Built-in MFA (New in v4)

```php
// In your Panel configuration
->mfa()
    ->totpEnabled()
    ->emailEnabled();
```

---

## Testing Filament

### Setup

```php
use function Livewire\livewire;
use Filament\Facades\Filament;

// For multi-panel, set current panel
Filament::setCurrentPanel('admin');
```

### Table Tests

```php
it('can list posts', function () {
    $posts = Post::factory()->count(5)->create();
    
    livewire(ListPosts::class)
        ->assertCanSeeTableRecords($posts)
        ->searchTable($posts->first()->title)
        ->assertCanSeeTableRecords($posts->take(1));
});
```

### Create Tests

```php
it('can create a post', function () {
    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'My Post',
            'body' => 'Content here',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Post::class, ['title' => 'My Post']);
});
```

### Action Tests

```php
it('can send invoice', function () {
    $invoice = Invoice::factory()->create();
    
    livewire(EditInvoice::class, ['invoice' => $invoice])
        ->callAction('send');
    
    expect($invoice->refresh())->isSent()->toBeTrue();
});
```

---

## Best Practices

### Use Artisan Commands

```bash
# Always use Filament's commands
php artisan make:filament-resource Post --generate
php artisan make:filament-page Settings
php artisan make:filament-widget StatsOverview --stats-overview

# Don't create files manually
```

### Relationships with `relationship()`

```php
// ✅ Use relationship() for auto-loading options
Select::make('user_id')
    ->relationship('author', 'name')
    ->required();

// ❌ Don't manually fetch options when relationship exists
Select::make('user_id')
    ->options(User::pluck('name', 'id'));
```

### Treat Like HTTP Requests

```php
// Validate and authorize in actions
protected function mutateFormDataBeforeCreate(array $data): array
{
    $this->authorize('create', Post::class);
    return $data;
}
```

### Production Access Control

```php
// Implement FilamentUser contract
class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
```

### Optimize for Production

```bash
php artisan filament:optimize
php artisan icons:cache
```
