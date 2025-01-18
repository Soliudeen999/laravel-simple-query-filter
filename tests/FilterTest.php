<?php

namespace Soliudeen999\QueryFilter\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Soliudeen999\QueryFilter\Traits\HasFilter;

class FilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test table
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->integer('age')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return ['Soliudeen999\QueryFilter\Providers\QueryFilterServiceProvider'];
    }

    /** @test */
    public function it_can_filter_using_basic_where_clause()
    {
        $model = new class extends Model {
            use HasFilter;
            protected $table = 'test_models';
            protected array $filterables = ['name', 'email'];
        };

        $query = $model->filter(['name' => 'John']);
        $this->assertStringContainsString(
            'where',
            $query->toSql()
        );
        $this->assertStringContainsString(
            'name',
            $query->toSql()
        );
    }

    /** @test */
    public function it_can_filter_using_operators()
    {
        $model = new class extends Model {
            use HasFilter;
            protected $table = 'test_models';
            protected array $filterables = ['age'];
        };

        $query = $model->filter(['age' => ['gt' => 18]]);
        $this->assertStringContainsString(
            'where',
            $query->toSql()
        );
        $this->assertStringContainsString(
            'age',
            $query->toSql()
        );
        $this->assertStringContainsString(
            '>',
            $query->toSql()
        );
    }

    /** @test */
    public function it_can_filter_using_between_operator()
    {
        $model = new class extends Model {
            use HasFilter;
            protected $table = 'test_models';
            protected array $filterables = ['age'];
        };

        $query = $model->filter(['age' => ['btw' => [18, 65]]]);
        $this->assertStringContainsString(
            'between',
            strtolower($query->toSql())
        );
    }

    /** @test */
    public function it_can_handle_empty_filters()
    {
        $model = new class extends Model {
            use HasFilter;
            protected $table = 'test_models';
            protected array $filterables = ['name', 'email'];
        };

        $query = $model->filter([]);
        $this->assertEquals(
            trim(preg_replace('/\s+/', ' ', $model->newQuery()->toSql())),
            trim(preg_replace('/\s+/', ' ', $query->toSql()))
        );
    }
}
