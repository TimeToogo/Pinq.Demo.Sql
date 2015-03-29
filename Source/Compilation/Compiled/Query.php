<?php

namespace Pinq\Demo\Sql\Compilation\Compiled;

use Pinq\Providers\DSL\Compilation\CompiledQuery;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterRegistry;
use Pinq\Queries\IResolvedParameterRegistry;

class Query extends CompiledQuery
{
    /**
     * @var string
     */
    protected $queryString;

    public function __construct($queryString, ParameterRegistry $parameters)
    {
        parent::__construct($parameters);
        $this->queryString = $queryString;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Creates a prepared query on the supplied connection.
     *
     * @param \PDO $connection
     *
     * @return \PDOStatement
     */
    public function buildStatement(\PDO $connection)
    {
        return $connection->prepare($this->queryString);
    }

    /**
     * Binds the resolved parameters to the supplied PDO statement.
     *
     * @param \PDOStatement              $statement
     * @param IResolvedParameterRegistry $resolvedParameters
     *
     * @return void
     */
    public function bindParameters(\PDOStatement $statement, IResolvedParameterRegistry $resolvedParameters)
    {
        $resolvedExpressions = $this->parameters->resolve($resolvedParameters);

        foreach($resolvedExpressions->getParameters() as $parameter) {
            $statement->bindValue($parameter->getData(), $resolvedExpressions->getValue($parameter));
        }
    }
}
