# Schema.org builder and JSON-LD generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![Total Downloads](https://img.shields.io/packagist/dt/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)

This package is used for building schema according to rules given on Schema.org  in JSON-LD format. User can pass node properties for which schema should be created. Relation between nodes will also be created.

## Installation

You can install the package via composer:

```bash
composer require trinityrank/laravel-schema-org-builder
```

## Usage

Use `Trinityrank\LaravelSchemaOrgBuilder\SchemaOrgBuilder`. For ease of use, in each class you should initialize it inside `__construct()`:

```php
public function __construct() 
{
    $this->schema_builder = new SchemaOrgBuilder();
}
```

To build the schema, you need to call `getSchemaOrg(...)` function which accepts next parameters:

```
 $entity - entity containing all the information, for which the schema is being built
 $node_properties - specific schema nodes for given entity, that should be created during this proces. At the end of this code block an example is given, containing nodes for some of the entities.
 $config - this parameter is optional. If available, here you should pass additional data in form of an array. At the moment available options are 'seo' and 'breadcrumbs'.

Example for '$node_properties':
[
    'home' => ['Organization', 'WebSite', 'WebPage'],
    'blog' => ['Organization', 'WebSite', 'WebPage', 'Article'],
    'news' => ['Organization', 'WebSite', 'WebPage', 'Article'],
    'money-page' => ['Organization', 'WebSite', 'WebPage', 'MoneyPage'],
    'review' => ['Organization', 'WebSite', 'WebPage', 'Review'],
    'blog-category' => ['Organization', 'WebSite', 'CollectionPage'],
    'blog-archive' => ['Organization', 'WebSite', 'CollectionPage'],
    'news-category' => ['Organization', 'WebSite', 'CollectionPage'],
    'news-archive' => ['Organization', 'WebSite', 'CollectionPage'],
    'reviews-category' => ['Organization', 'WebSite', 'CollectionPage'],
    'reviews-archive' => ['Organization', 'WebSite', 'CollectionPage'],
    'money-page-category' => ['Organization', 'WebSite', 'CollectionPage'],
]
```

Some of the data is being retrieved from config files.

From `main.php`:
```
- main.seo.home.meta_description
- main.mail_address
```
From package's config file `schema-org-builder.php`, that you should publish using `php artisan vendor:publish --tag="schema-org-builder"`:
```
- schema-org-builder.general.logo
- schema-org-builder.general.name
- schema-org-builder.sameAs
- schema-org-builder.slogan
- schema-org-builder.general.inLanguage
```
## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
