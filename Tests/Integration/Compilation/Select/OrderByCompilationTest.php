<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\Direction;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderBySqlCompilationTest extends SqlSelectQueryCompilationTest
{
    public function queryTests()
    {
        return [
            [
                'numbers',
                [[function ($row) { return $row['x']; }, Direction::ASCENDING]],
                'SELECT * FROM (SELECT * FROM numbers) AS numbers ORDER BY numbers.x ASC'
            ],
            [
                'numbers',
                [[function ($row) { return $row['x']; }, Direction::DESCENDING]],
                'SELECT * FROM (SELECT * FROM numbers) AS numbers ORDER BY numbers.x DESC'
            ],
            [
                'numbers',
                [[function ($row) { return -$row['x']; }, Direction::ASCENDING]],
                'SELECT * FROM (SELECT * FROM numbers) AS numbers ORDER BY (-numbers.x) ASC'
            ],
            [
                'numbers',
                [
                    [function ($row) { return ($row['x'] / 10 - $row['x'] % 10); }, Direction::ASCENDING],
                    [function ($row) { return $row['x']; }, Direction::DESCENDING]
                ],
                'SELECT * FROM (SELECT * FROM numbers) AS numbers ORDER BY ((numbers.x / 10) - (numbers.x % 10)) ASC, numbers.x DESC'
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $testData)
    {
        $table = $table->orderBy($testData[0][0], $testData[0][1]);

        foreach (array_slice($testData, 1) as $expression) {
            $table = $table->thenBy($expression[0], $expression[1]);
        }

        return $table;
    }
}