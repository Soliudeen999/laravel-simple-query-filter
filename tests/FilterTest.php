<?php

namespace Soliudeen999\QueryFilter\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Database\Eloquent\Model;
use Soliudeen999\QueryFilter\Traits\HasFilter;

class FilterTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Soliudeen999\QueryFilter\Providers\QueryFilterServiceProvider'];
    }

    /** @test */
    public function it_can_filter_using_basic_where_clause()
    {
        $model = new class extends Model {
            use HasFilter;
            protected array $filterables = ['name', 'email'];
        };

        $query = $model->filter(['name' => 'John']);
        $this->assertEquals(
            'select * from "' . $model->getTable() . '" where "name" = ?',
            $query->toSql()
        );
    }

    /** @test */
    public function it_can_filter_using_operators()
    {
        $model = new class extends Model {
            use HasFilter;
            protected array $filterables = ['age'];
        };

        $query = $model->filter(['age' => ['gt' => 18]]);
        $this->assertEquals(
            'select * from "' . $model->getTable() . '" where "age" > ?',
            $query->toSql()
        );
    }

    // Add more tests...
} 