<?php

namespace Soliudeen999\QueryFilter\Tests;

use Illuminate\Database\Eloquent\Model;
use Soliudeen999\QueryFilter\Traits\HasFilter;

class TestModel extends Model
{
    use HasFilter;

    protected $table = 'test_models';
    protected array $filterables = ['name', 'email', 'age'];

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
    }
}
