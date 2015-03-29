<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Delete;

use Pinq\Demo\Sql\Tests\Integration\Compilation\SqlQueryCompilationTest;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SqlDeleteQueryCompilationTest extends SqlQueryCompilationTest
{
    protected function doTestQuery(IRepository $table, array $testData)
    {
        // This should clear the applicable result rows
        // hence the query will be compiled to a DELETE query.
        $this->executeTestQueryScope($table, $testData)->clear();
    }

    /**
     * @param IRepository $table
     * @param array $testData
     * @return IRepository
     */
    protected abstract function executeTestQueryScope(IRepository $table, array $testData);
}