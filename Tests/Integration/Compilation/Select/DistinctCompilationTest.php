<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\Demo\Sql\Tests\Integration\Compilation\SqlAssertsIgnoringWhitespace;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DistinctCompilationTest extends SqlSelectQueryCompilationTest
{
    use SqlAssertsIgnoringWhitespace;

    public function queryTests()
    {
        return [
            [
                'data',
                null,
                'SELECT DISTINCT * FROM (SELECT * FROM data) AS data'
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $queries)
    {
        return $table->unique();
    }
}