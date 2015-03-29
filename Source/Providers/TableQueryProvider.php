<?php

namespace Pinq\Demo\Sql\Providers;

use PDO;
use Pinq\Demo\Sql\Compilation\Compiled\Select;
use Pinq\Providers\Configuration;
use Pinq\Providers\DSL\Compilation;
use Pinq\Providers\DSL\IQueryCompilerConfiguration;
use Pinq\Providers\DSL\QueryProvider;
use Pinq\Queries;
use Pinq\Demo\Sql;

/**
 * Implementation of the table provider.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableQueryProvider extends QueryProvider
{
    /**
     * @var PDO
     */
    protected $connection;

    public function __construct(
            PDO $connection,
            TableSourceInfo $sourceInfo,
            IQueryCompilerConfiguration $compilerConfiguration
    ) {
        parent::__construct($sourceInfo, $compilerConfiguration);

        $this->connection = $connection;
    }

    protected function loadCompiledRequest(
            Compilation\ICompiledRequest $compiledRequest,
            Queries\IResolvedParameterRegistry $parameters
    ) {
        /** @var $compiledRequest Select */

        return $compiledRequest->getResults($this->connection, $parameters);
    }
}