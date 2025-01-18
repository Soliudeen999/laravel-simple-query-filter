<?php

namespace Soliudeen999\QueryFilter\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->integer('age')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        parent::tearDown();
    }

    /** @test */
    public function it_can_filter_using_basic_where_clause(): void
    {
        $model = new TestModel();
        $query = $model->filter(['name' => 'John']);
        
        $this->assertStringContainsString('where', $query->toSql());
        $this->assertStringContainsString('name', $query->toSql());
    }

    /** @test */
    public function it_can_filter_using_operators(): void
    {
        $model = new TestModel();
        $query = $model->filter(['age' => ['gt' => 18]]);
        
        $sql = $query->toSql();
        $this->assertStringContainsString('where', $sql);
        $this->assertStringContainsString('age', $sql);
        $this->assertStringContainsString('>', $sql);
    }

    /** @test */
    public function it_can_filter_using_between_operator(): void
    {
        $model = new TestModel();
        $query = $model->filter(['age' => ['btw' => [18, 65]]]);
        
        $this->assertStringContainsString('between', strtolower($query->toSql()));
    }

    /** @test */
    public function it_can_handle_empty_filters(): void
    {
        $model = new TestModel();
        $query = $model->filter([]);
        $baseQuery = $model->newQuery();
        
        $this->assertEquals(
            trim(preg_replace('/\s+/', ' ', $baseQuery->toSql())),
            trim(preg_replace('/\s+/', ' ', $query->toSql()))
        );
    }

    /** @test */
    public function it_can_search_and_filter(): void
    {
        $model = new TestModel();
        $query = $model->filterSearchLoad(
            filters: ['status' => 'active'],
            searchKeyword: 'john'
        );
        
        $sql = $query->toSql();
        $this->assertStringContainsString('where', $sql);
        $this->assertStringContainsString('LIKE', strtoupper($sql));
    }
}
