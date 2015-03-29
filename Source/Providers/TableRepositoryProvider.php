<?php

namespace Pinq\Demo\Sql\Providers;

use Pinq\Demo\Sql\Compilation\Compiled\UpdateOrDelete;
use Pinq\Demo\Sql\Compilation\SqlCompilerConfiguration;
use Pinq\Demo\Sql;
use Pinq\Providers\Configuration;
use Pinq\Providers\DSL\Compilation;
use Pinq\Providers\DSL\RepositoryProvider;
use Pinq\Queries;

/**
 * Implementation of the table provider.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRepositoryProvider extends RepositoryProvider
{
    /**
     * @var \PDO
     */
    protected $connection;

    public function __construct(\PDO $connection, TableSourceInfo $table, SqlCompilerConfiguration $compilerConfiguration)
    {
        $queryProvider   = new TableQueryProvider($connection, $table, $compilerConfiguration);

        parent::__construct(
                $table,
                $compilerConfiguration,
                $queryProvider
        );

        $this->connection = $connection;
    }

    protected function executeCompiledOperation(
            Compilation\ICompiledOperation $compiledOperation,
            Queries\IResolvedParameterRegistry $parameters
    ) {
        /** @var $compiledOperation UpdateOrDelete */

        $compiledOperation->execute($this->connection, $parameters);
    }
}