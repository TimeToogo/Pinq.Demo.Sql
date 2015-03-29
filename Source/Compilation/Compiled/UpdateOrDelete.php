<?php

namespace Pinq\Demo\Sql\Compilation\Compiled;

use Pinq\Providers\DSL\Compilation\ICompiledOperation;
use Pinq\Queries\IResolvedParameterRegistry;

class UpdateOrDelete extends Query implements ICompiledOperation
{
    public function execute(\PDO $connection, IResolvedParameterRegistry $resolvedParameters)
    {
        $statement = $this->buildStatement($connection);

        $this->bindParameters($statement, $resolvedParameters);

        $statement->execute();
    }
}
