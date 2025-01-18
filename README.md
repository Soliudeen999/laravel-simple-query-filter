# Laravel Simple Query Filter

A powerful and flexible query filter system for Laravel applications that allows you to easily filter your Eloquent models based on request parameters.

## Installation

You can install the package via composer:

```bash
composer require soliudeen999/laravel-simple-query-filter
```

## Setup

Add the `HasFilter` trait to your model and define the filterable fields:

```php
use Soliudeen999\QueryFilter\Traits\HasFilter;

class User extends Model
{
    use HasFilter;

    protected array $filterables = [
        'name',
        'email',
        'status',
        'role' => ['admin', 'user', 'guest'],
        'posts' => 'posts:relationship,title',
        'withTrashed' => ['with', 'only']
    ];
}
```

## Usage

### Basic Filtering 

```php
// Filter using request parameters automatically
$users = User::filter()->get();

// Filter with specific parameters
$users = User::filter(['status' => 'active'])->get();

// Filter multiple fields
$users = User::filter([
    'status' => 'active',
    'role' => 'admin'
])->get();
```

### Advanced Filtering

#### Comparison Operators

The following operators are supported:
- `gt` (greater than)
- `lt` (less than)
- `eq` (equals)
- `neq` (not equals)
- `gte` (greater than or equal)
- `lte` (less than or equal)
- `btw` (between)
- `in` (in array)

```php
// Using operators
$users = User::filter([
    'age' => ['gt' => 18, 'lt' => 65],
    'status' => ['in' => ['active', 'pending']],
    'price' => ['btw' => [100, 200]],
    'rating' => ['gte' => 4.5]
])->get();
```

#### Relationship Filtering

Define relationship filters in your `$filterables` array:

```php
protected array $filterables = [
    'posts' => 'posts:relationship,title', // format: 'table:relationship,column'
    'comments' => 'comments:relationship,content'
];

// Usage
$users = User::filter([
    'posts' => 'Laravel', // Find users with posts containing 'Laravel' in title
    'comments' => ['active'] // Find users with these comment types
])->get();
```

#### Special Values Filtering

Define allowed values in your `$filterables` array:

```php
protected array $filterables = [
    'role' => ['admin', 'user', 'guest'],
    'status' => ['active', 'inactive']
];

// Usage
$users = User::filter([
    'role' => 'admin',  // Will only filter if 'admin' is in allowed values
    'status' => 'active'
])->get();
```

### Sorting

```php
// Ascending order
$users = User::filter()->get(); // ?sort=name

// Descending order
$users = User::filter()->get(); // ?sort=-name
```

### Combined Search and Eager Loading

The `filterSearchLoad` scope combines filtering, searching, and eager loading:

```php
// Basic usage
$users = User::filterSearchLoad(
    filters: ['status' => 'active'],
    searchKeyword: 'john',
    loads: 'posts,comments'
)->get();

// Using request parameters
$users = User::filterSearchLoad()->get();
```

### Request Parameters

When using request parameters, you can filter using query string parameters:

```
/users?name=John&status=active&age[gt]=18&age[lt]=65&sort=-created_at
```

### Custom Search Scope

To enable search functionality, define a `scopeSearch` method in your model:

```php
public function scopeSearch($query, $keyword)
{
    return $query->where('name', 'LIKE', "%{$keyword}%")
                 ->orWhere('email', 'LIKE', "%{$keyword}%");
}
```

## Best Practices

1. Always define the `$filterables` array in your model to specify which fields can be filtered
2. Use type hints and validation where possible
3. Keep the filter values consistent with your database schema
4. Use relationship filters for complex queries
5. Implement custom search scopes for specific search requirements

## Error Handling

The package will throw exceptions for:
- Missing `$filterables` property
- Invalid relationship filter configurations
- Invalid operator usage
- Invalid between clause values

Make sure to handle these exceptions appropriately in your application.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email soliudeen999@gmail.com instead of using the issue tracker.

## Credits

- [Soliudeen999](https://github.com/soliudeen999)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.