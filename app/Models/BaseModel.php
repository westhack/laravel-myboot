<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Models;

use App\Foundation\Model\BatchUpdateTrait;
use App\Foundation\Model\ScopeFirstOrErrorTrait;
use DateTimeInterface;
use EloquentSearch\SearchTrait;
use EloquentSearch\SortOrderTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;
use Spiritix\LadaCache\Database\Pivot;
use \App\Models\Database\QueryBuilder;

class BaseModel extends Model
{
    use BatchUpdateTrait;
    use SearchTrait;
    use SortOrderTrait;
    use ScopeFirstOrErrorTrait;

    public $isCache = false;

    public static function getTableName()
    {
        return env('DB_PRE') . with(new static())->getTable();
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return $this->fromFloat($value);
            case 'decimal':
                return $this->asDecimal($value, explode(':', $this->getCasts()[$key], 2)[1]);
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return $this->fromJson($value, true);
            case 'array':
            case 'json':
                return $this->fromJson($value);
            case 'collection':
                return new BaseCollection($this->fromJson($value));
            case 'date':
                return $this->asDate($value);
            case 'datetime':
            case 'custom_datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimestamp($value);
            default:
                return $value;
        }
    }

    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Create a new pivot model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * @param  string|null  $using
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        $queryCache = config('cache.queryCache');
        if ($this->isCache || $queryCache) {
            return $using ? $using::fromRawAttributes($parent, $attributes, $table, $exists)
                : Pivot::fromAttributes($parent, $attributes, $table, $exists);
        } else {
            return parent::newPivot($parent, $attributes, $table, $exists, $using);
        }
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();
        $grammar = $conn->getQueryGrammar();

        return new QueryBuilder(
            $conn,
            $grammar,
            $conn->getPostProcessor(),
            app()->make('lada.handler'),
            $this
        );
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if ($method == 'cache') {
            $new = (new static);

            $new->isCache = true;

            return $new->newQuery();
        }

        return (new static)->$method(...$parameters);
    }
}
