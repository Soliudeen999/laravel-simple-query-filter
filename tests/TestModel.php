<?php

namespace Soliudeen999\QueryFilter\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Soliudeen999\QueryFilter\Traits\HasFilter;

/**
 * @method static Builder filter(?array $filters = null)
 * @method static Builder filterSearchLoad(?array $filters = null, ?string $searchKeyword = null, string $loads = '')
 */
class TestModel extends Model
{
    use HasFilter;

    protected $table = 'test_models';
    protected array $filterables = ['name', 'email', 'age'];

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
    }
}
