## Simple E-Shop Management with Front-end Theme

This project demonstrates how to use Filament as admin panel for products/categories and have a totally separate front-facing website based on Bootstrap CSS.

The repository contains the complete Laravel + Filament project to demonstrate the functionality, including migrations/seeds for the demo data.

Feel free to pick the parts that you actually need in your projects.

---

## How to install

- Clone the repository with `git clone`
- Copy the `.env.example` file to `.env` and edit database credentials there
- Run `composer install`
- Run `php artisan key:generate`
- Run `php artisan migrate --seed` (it has some seeded data for your testing)
- Run `php artisan storage:link`
- That's it: launch the main URL and see the front homepage. Also, launch the URL `/admin` and log in with credentials `admin@admin.com` and `password` to manage products.

---

## Screenshots

![](https://laraveldaily.com/uploads/2023/09/filament-eshop-front.png)

![](https://laraveldaily.com/uploads/2023/09/filament-eshop-admin.png)


---

## How It Works

For the admin panel, we have three resources:

- Categories
- Products
- Orders

Categories resource only has a `name` and `slug` fields. Slug is auto-generated when the focus is removed from the name input. Here's how the form looks:

```php
Forms\Components\TextInput::make('name')
    ->required()
    ->live(onBlur: true)
    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
Forms\Components\TextInput::make('slug')
    ->disabled()
    ->dehydrated()
    ->required()
    ->unique(Category::class, 'slug', ignoreRecord: true),
```

---

Product Resource has a name and auto-generated slug inputs, description as simple textarea. Price and price before discount number inputs with a step of `0.01`. Here is how we are setting the step for price inputs.

```php
Forms\Components\TextInput::make('price')
    ->required()
    ->numeric()
    ->step(0.01),
Forms\Components\TextInput::make('price_before_discount')
    ->numeric()
    ->step(0.01),
```

Prices are saved in DB in cents. To achieve it automatically, we are using [accessors and mutators](https://laravel.com/docs/eloquent-mutators#accessors-and-mutators). This is how they look in the Product Model.

```php
protected function price(): Attribute
{
    return Attribute::make(
        get: fn($value) => $value / 100,
        set: fn($value) => $value * 100,
    );
}

protected function priceBeforeDiscount(): Attribute
{
    return Attribute::make(
        get: fn($value) => $value / 100,
        set: fn($value) => $value * 100,
    );
}
```

To upload images for a product, we use [the official plugin](https://filamentphp.com/plugins/filament-spatie-media-library) for [spatie/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary/introduction) package. The form field code looks like this:

```php
Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
    ->columnSpanFull(),
```

When uploading an image for a product, we are creating two conversions:

- `thumb` for showing in an all-products list
- `front_large` for showing on the product page

Conversions are defined in the Product Model:

```php
public function registerMediaConversions(Media $media = null): void
{
    $this->addMediaConversion('front_thumb')
        ->fit('crop', 450, 300);

    $this->addMediaConversion('front_large')
        ->fit('crop', 600);
}
```

---

The Orders Resource has only one page for listing orders. We also have a summarizer SUM for a price.

---

To manage settings, we use [an official plugin](https://filamentphp.com/plugins/filament-spatie-settings) for a [spatie/laravel-settings](https://github.com/spatie/laravel-settings) package.

For settings, we manage the following:

- Title
- Subtitle
- About
- Logo

The settings are defined as `general` and are set as follows:

```php
class GeneralSettings extends Settings
{
    public string $title;
    public string $subTitle;
    public string $about;
    public string|null $logo;

    public static function group(): string
    {
        return 'general';
    }
}
```

Here is how the form looks for the settings page:

```php
Forms\Components\TextInput::make('title'),
Forms\Components\TextInput::make('subTitle'),
Forms\Components\Textarea::make('about')
    ->columnSpanFull(),
Forms\Components\FileUpload::make('logo')
    ->columnSpanFull(),
```

---

In the panel dashboard, we have four widgets.

The first widget shows how many orders are made this day with a percentage of increase or decrease since yesterday.

Next, we have two chart widgets. One shows revenue for this month by day and the second shows this week by day.

In the last widget, we show a table of 5 latest orders.

---

For the frontend, we use a free [bootstrap template](https://startbootstrap.com/template/shop-homepage).

In the navigation, we show the logo set in the settings page. This is how we are showing it:

```blade
<img src="{{ asset(app(\App\Settings\GeneralSettings::class)->logo) }}" alt="{{ app(\App\Settings\GeneralSettings::class)->title }}">
```

We are showing products in a grid list on the home page and category page. These two pages show product image from the `thumb` conversation. This is how we show it:

```blade
<img class="card-img-top" src="{{ $product->getFirstMediaUrl(conversionName: 'thumb') }}" alt="{{ $product->name }}" />
```

In the home page header section, we show the title and sub-title. This is how we show them:

```blade
<x-slot name="header">
    <h1 class="display-4 fw-bolder">{{ app(\App\Settings\GeneralSettings::class)->title }}</h1>
    <p class="lead fw-normal text-white-50 mb-0">{{ app(\App\Settings\GeneralSettings::class)->subTitle }}</p>
</x-slot>
```

We show an image from the `front_large` conversation for the product page. This is how we show it:

```blade
<img class="card-img-top mb-5 mb-md-0" src="{{ $product->getFirstMediaUrl(conversionName: 'front_large') }}" alt="{{ $product->name }}" />
```

In the About page, we show the text set in the settings. This is how we show the text:

```blade
<div class="lead">
    {{ app(\App\Settings\GeneralSettings::class)->about }}
</div>
```
