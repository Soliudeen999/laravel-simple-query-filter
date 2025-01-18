<?php

namespace Soliudeen999\QueryFilter\Traits;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

trait HasFilter
{
    private array $allowedComparisonOperators = ['gt', 'lt', 'eq', 'btw', 'in', 'neq', 'gte', 'lte'];

    public function scopeFilter(Builder $query, ?array $filters = null): Builder
    {
        if (!property_exists($this, 'filterables')) {
            throw new InvalidArgumentException('$filterables property not defined in this model');
        }

        // If no filters provided, use request parameters that match filterables
        $filters = $filters ?? request()->only(
            array_merge(
                array_intersect(array_keys(request()->query()), $this->filterables),
                array_intersect(array_keys(request()->query()), array_keys($this->filterables))
            )
        );

        if (empty($filters)) {
            return $query;
        }

        if (!empty($this->filterables)) {
            foreach ($filters as $column => $value) {
                if ($this->isFilterableColumn($column)) {
                    $this->applyFilter($query, $column, $value);
                }
            }
        }

        // Apply sorting if requested
        if (request()->has('sort')) {
            $this->applySorting($query, request('sort'));
        }

        return $query;
    }

    protected function isFilterableColumn(string $column): bool
    {
        return in_array($column, $this->filterables) || 
               array_key_exists($column, $this->filterables);
    }

    protected function applyFilter(Builder $query, string $column, $value): void
    {
        // Handle relationship filters
        if ($this->isRelationshipFilter($column)) {
            $this->applyRelationshipFilter($query, $column, $value);
            return;
        }

        // Handle array filters with operators
        if (is_array($value) && $this->hasOperators($value)) {
            $this->applyOperatorFilters($query, $column, $value);
            return;
        }

        // Handle special values
        if ($this->isSpecialFilter($column)) {
            $this->applySpecialFilter($query, $column, $value);
            return;
        }

        // Default where clause
        $query->where($column, $value);
    }

    protected function isRelationshipFilter(string $column): bool
    {
        return isset($this->filterables[$column]) && 
               is_string($this->filterables[$column]) && 
               str_contains($this->filterables[$column], ':');
    }

    protected function applyRelationshipFilter(Builder $query, string $column, $value): void
    {
        $related = explode(':', $this->filterables[$column]);
        $table = $related[0];

        try {
            [$relationship, $relationshipColumn] = explode(',', $related[1]);
            
            $query->whereHas($relationship, function ($q) use ($relationshipColumn, $value, $table) {
                if (is_array($value)) {
                    $q->whereIn($table . '.' . $relationshipColumn, $value);
                } else {
                    $q->where($table . '.' . $relationshipColumn, $value);
                }
            });
        } catch (\Exception $error) {
            throw new InvalidArgumentException(
                "Invalid relationship filter configuration: " . $error->getMessage()
            );
        }
    }

    protected function hasOperators(array $value): bool
    {
        return count(array_intersect(array_keys($value), $this->allowedComparisonOperators)) > 0;
    }

    protected function applyOperatorFilters(Builder $query, string $column, array $value): void
    {
        foreach ($value as $operator => $data) {
            if (in_array($operator, $this->allowedComparisonOperators)) {
                $this->buildQuery($query, $column, $operator, $data);
            }
        }
    }

    protected function isSpecialFilter(string $column): bool
    {
        return isset($this->filterables[$column]) && is_array($this->filterables[$column]);
    }

    protected function applySpecialFilter(Builder $query, string $column, $value): void
    {
        if (!in_array($value, $this->filterables[$column])) {
            return;
        }

        if ($column === 'withTrashed' && $this->usesSoftDeletes()) {
            $value === 'with' ? $query->withTrashed() : $query->onlyTrashed();
        } else {
            $query->where($column, $value);
        }
    }

    protected function usesSoftDeletes(): bool
    {
        return in_array(
            "Illuminate\Database\Eloquent\SoftDeletes", 
            class_uses_recursive($this)
        );
    }

    protected function buildQuery(Builder $query, string $field, string $operator, $data): Builder
    {
        $operator = $this->normalizeOperator($operator);
        
        if ($this->isJsonArray($data)) {
            $data = json_decode($data);
        }

        if ($operator === 'between' && (!is_array($data) || count($data) !== 2)) {
            return $query;
        }

        return match($operator) {
            'between' => $query->whereBetween($field, $data),
            'in' => $query->whereIn($field, (array)$data),
            default => $query->where($field, $operator, $data)
        };
    }

    protected function normalizeOperator(string $operator): string
    {
        return match ($operator) {
            'btw' => 'between',
            'in' => 'in',
            'gt' => '>',
            'lt' => '<',
            'lte' => '<=',
            'gte' => '>=',
            'neq' => '<>',
            'eq' => '=',
            default => $operator
        };
    }

    protected function isJsonArray(string|array $data): bool
    {
        if (!is_string($data)) {
            return false;
        }
        
        $array = json_decode($data, true);
        return is_array($array) && json_last_error() === JSON_ERROR_NONE;
    }

    protected function applySorting(Builder $query, string $sort): void
    {
        $direction = 'asc';
        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $sort = substr($sort, 1);
        }

        if ($this->isFilterableColumn($sort)) {
            $query->orderBy($sort, $direction);
        }
    }

    public function scopeFilterSearchLoad(Builder $query, ?array $filters = null, $searchKeyword = null, string $loads = ''): Builder
    {
        $filters = $filters ?? request()->except(['search']);
        $builder = $this->scopeFilter($query, $filters);

        $searchKeyword = $searchKeyword ?? request('search');
        if ($searchKeyword && method_exists($this, 'scopeSearch')) {
            $builder = $this->scopeSearch($builder, $searchKeyword);
        }

        if ($loads) {
            $relations = array_filter(explode(',', $loads));
            if (!empty($relations)) {
                $builder->with($relations);
            }
        }

        return $builder;
    }
} 