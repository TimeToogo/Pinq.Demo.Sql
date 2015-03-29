<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelectSqlCompilationTest extends SqlSelectQueryCompilationTest
{
    public function queryTests()
    {
        return [
            [
                'numbers',
                function ($row) {
                    return [
                        'x' => $row['x']
                    ];
                },
                'SELECT numbers.x AS x FROM (SELECT * FROM numbers) AS numbers'
            ],
            [
                '___',
                function ($row) {
                    return [
                        'x' => $row['x'],
                        'y' => $row['x'] * 100,
                    ];
                },
                'SELECT ___.x AS x, (___.x * 100) AS y FROM (SELECT * FROM ___) AS ___'
            ],
            [
                '___',
                function ($row) {
                    return [
                        'x' => $row['x'],
                        'x' => $row['x'] * 100,
                    ];
                },
                'SELECT ___.x AS x, (___.x * 100) AS x FROM (SELECT * FROM ___) AS ___'
            ],
            [
                'data',
                function ($row) {
                    return [
                        'str'     => $row['x'] . '--',
                        'average' => $row['total'] / $row['amount'],
                    ];
                },
                "SELECT CONCAT(data.x, '!!--!!') AS str, (data.total / data.amount) AS average FROM (SELECT * FROM data) AS data"
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $testData)
    {
        foreach ($testData as $expression) {
            $table = $table->select($expression);
        }

        return $table;
    }
}