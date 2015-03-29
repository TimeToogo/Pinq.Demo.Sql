<?php

namespace Pinq\Demo\Sql\Tests\Integration\Query;

use Pinq\Demo\Sql\Tests\MockedPdoDemoSqlTestCase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InMemorySubScopeQueryOptimizationTest extends MockedPdoDemoSqlTestCase
{
    public function testOnlyDoesOneQueryWhenAParentQueryIsLoadedAndASubScopeQueryIsExecuted()
    {
        $asArrayResults = [
            ['x' => 4],
            ['x' => 5],
            ['x' => 6],
            ['x' => 7],
            ['x' => 8],
            ['x' => 9],
            ['x' => 10],
        ];

        $this->pdo->setFetchAllQueryResultSet($asArrayResults);
        $this->assertCount(0, $this->pdo->getExecutedStatements());

        $query = $this->db
            ->table('some_table')
            ->where(function ($row) { return $row['x'] >= 3 ;});
        $results = $query->asArray();

        $this->assertCount(1, $this->pdo->getExecutedStatements());
        $this->assertSame($asArrayResults, $results);

        $executedInPhp = false;
        $subScopeQuery = $query
            ->where(function ($row) use (&$executedInPhp) {
                $executedInPhp = true;
                return $row['x'] < 7;
            });
        $filteredResults = $subScopeQuery->asArray();

        $this->assertCount(1, $this->pdo->getExecutedStatements());
        $this->assertSame([
            ['x' => 4],
            ['x' => 5],
            ['x' => 6],
        ], $filteredResults);
        $this->assertTrue($executedInPhp);
    }
}