<?php

namespace Pinq\Demo\Sql\Compilation\Compiled;

use Pinq\Collection;
use Pinq\Demo\Sql\PinqDemoSqlException;
use Pinq\Providers\DSL\Compilation\ICompiledRequest;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterRegistry;
use Pinq\Queries\IResolvedParameterRegistry;
use Pinq\Queries\Requests\Values;
use Pinq\Traversable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Select extends Query implements ICompiledRequest
{
    protected $retrievalMode;

    public function __construct($queryString, ParameterRegistry $parameters, $retrievalMode)
    {
        parent::__construct($queryString, $parameters);
        $this->retrievalMode = $retrievalMode;
    }

    /**
     * Retrieves the result set of the SELECT query.
     *
     * @param \PDO                       $connection
     * @param IResolvedParameterRegistry $resolvedParameters
     *
     * @return array|\Traversable
     */
    public function getResults(\PDO $connection, IResolvedParameterRegistry $resolvedParameters)
    {
        $statement = $this->buildStatement($connection);

        $this->bindParameters($statement, $resolvedParameters);
        $statement->execute();

        return $this->retrieveValues($statement);
    }

    protected function retrieveValues(\PDOStatement $statement)
    {
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        switch ($this->retrievalMode) {
            case Values::AS_ARRAY:
                return $statement->fetchAll();

            case Values::AS_ARRAY_COMPATIBLE_ITERATOR:
                return new \IteratorIterator($statement);

            case Values::AS_TRUE_ITERATOR:
                return new \IteratorIterator($statement);

            case Values::AS_TRAVERSABLE:
                return Traversable::from($statement);

            case Values::AS_COLLECTION:
                return Collection::from($statement);

            default:
                throw new PinqDemoSqlException('Unsupported value retrieval mode');
        }
    }
} 