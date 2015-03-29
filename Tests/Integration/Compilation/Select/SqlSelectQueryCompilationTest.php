<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\Demo\Sql\Tests\Integration\Compilation\SqlQueryCompilationTest;
use Pinq\Expressions\Expression;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SqlSelectQueryCompilationTest extends SqlQueryCompilationTest
{
    protected function doTestQuery(IRepository $table, array $testData)
    {
        // This should return the applicable result set as an associative array
        // hence the query will be compiled to a SELECT query to retrieve the results.
        $this->executeTestQueryScope($table, $testData)->asArray();
    }

    /**
     * @param IRepository $table
     * @param array $testData
     * @return IRepository
     */
    protected abstract function executeTestQueryScope(IRepository $table, array $testData);
}