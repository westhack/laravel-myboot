<?php
/**
 * This file is part of the spiritix/lada-cache package.
 *
 * @copyright Copyright (c) Matthias Isler <mi@matthias-isler.ch>
 * @license   MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Models\Database;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Spiritix\LadaCache\QueryHandler;
use Spiritix\LadaCache\Reflector;
use Spiritix\LadaCache\Database\QueryBuilder as Builder;

/**
 * Overrides Laravel's query builder class.
 *
 * @package Spiritix\LadaCache\Database
 * @author  Matthias Isler <mi@matthias-isler.ch>
 */
class QueryBuilder extends Builder
{

    /**
     * Handler instance.
     *
     * @var QueryHandler
     */
    private $handler;

    /**
     * Create a new query builder instance.
     *
     * @param  ConnectionInterface $connection
     * @param  Grammar             $grammar
     * @param  Processor           $processor
     * @param  QueryHandler        $handler
     * @param  Model               $model
     */
    public function __construct(ConnectionInterface $connection, Grammar $grammar, Processor $processor,
                                QueryHandler $handler, Model $model)
    {
        parent::__construct($connection, $grammar, $processor, $handler, $model);
        $this->handler = $handler;
        $this->model = $model;
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        $queryCache = config('cache.queryCache');
        if ($this->model->isCache == true || $queryCache == true) {
            return $this->handler->setBuilder($this)->cacheQuery(function() {
                return parent::runSelect();
            });
        } else {
            return $this->connection->select(
                $this->toSql(), $this->getBindings(), ! $this->useWritePdo
            );
        }
    }

    /**
     * Add a subselect expression to the query.
     *
     * @param  \Closure|static|string $query
     * @param  string  $as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function selectSub($query, $as)
    {
        $queryCache = config('cache.queryCache');
        if ($this->model->isCache == true || $queryCache == true) {
            $this->handler->setBuilder($query)
                ->collectSubQueryTags();

            return parent::selectSub($query, $as);
        } else {
            [$query, $bindings] = $this->createSub($query);

            return $this->selectRaw(
                '('.$query.') as '.$this->grammar->wrap($as), $bindings
            );
        }
    }
}
