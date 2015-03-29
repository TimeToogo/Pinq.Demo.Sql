<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WhereSqlCompilationTest extends SqlSelectQueryCompilationTest
{
    public function queryTests()
    {
        return [
            [
                'numbers',
                function ($row) { return $row['x'] >= 1; },
                'SELECT * FROM (SELECT * FROM numbers) AS numbers WHERE (numbers.x >= 1)'
            ],
            [
                '___',
                function ($row) { return $row['abc'] < -1; },
                'SELECT * FROM (SELECT * FROM ___) AS ___ WHERE (___.abc < (-1))'
            ],
            [
                'data',
                function ($row) { return $row['abc'] < -1 && $row['xyz'] > 5; },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE ((data.abc < (-1)) AND (data.xyz > 5))'
            ],
            [
                'data',
                [
                    function ($row) { return $row['abc'] < -1; },
                    function ($row) { return $row['xyz'] > 5; }
                ],
                'SELECT * FROM (SELECT * FROM (SELECT * FROM data) AS data WHERE (data.abc < (-1))) AS data WHERE (data.xyz > 5)'
            ],
            [
                'data',
                function ($row) { return strlen($row['string']) == 50; },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE (LENGTH(data.string) <=> 50)'
            ],
            [
                'data',
                function ($row) { return strlen($row['string']) % 2 == 0 ? 1 : 0; },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE CASE WHEN ((LENGTH(data.string) % 2) <=> 0) THEN 1 ELSE 0 END'
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $testData)
    {
        foreach($testData as $filter) {
            $table = $table->where($filter);
        }

        return $table;
    }
}