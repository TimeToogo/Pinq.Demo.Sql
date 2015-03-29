<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation;

use Pinq\Demo\Sql\Tests\MockedPdoDemoSqlTestCase;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SqlQueryCompilationTest extends MockedPdoDemoSqlTestCase
{
    public  abstract function queryTests();

    /**
     * @dataProvider queryTests
     */
    public function testQueryCompilation($table, $expressions, $sql, $bindings = [])
    {
        if(is_array($table)) {
            $table = $this->db->table($table['name'], $table['primary-keys']);
        } else {
            $table = $this->db->table($table);
        }

        $this->doTestQuery(
            $table,
            is_array($expressions) ? $expressions : [$expressions]
        );

        $this->assertLastExecutedQueryWas($sql, $bindings);
    }

    /**
     * @param IRepository $table
     * @param mixed[] $testData
     * @return void
     */
    protected abstract function doTestQuery(IRepository $table, array $testData);

    protected function assertLastExecutedQueryWas($sql, $bindings = [], $executedQueries = 1)
    {
        $this->assertCount($executedQueries, $this->pdo->getExecutedStatements(), 'The executed queries must be the correct amount');

        $lastStatement = $this->pdo->getLastExecutedStatement();
        $this->assertSqlEquals($sql, $lastStatement->sql, 'The last executed query must match');

        $actualBindings = $lastStatement->bindings;
        ksort($bindings);
        ksort($actualBindings);
        $this->assertSame($bindings, $actualBindings, 'The query bindings must match');
    }

    protected function assertSqlEquals($expected, $actual, $message = '')
    {
        $this->assertSame($expected, $actual, $message);
    }
}