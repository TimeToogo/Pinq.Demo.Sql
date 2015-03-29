<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StructuralParameterSqlCompilationTest extends SqlSelectQueryCompilationTest
{
    public function queryTests()
    {
        $function = 'strlen';

        return [
            [
                'data',
                function (IRepository $table) use ($function) {
                    return $table
                        ->where(function ($row) use ($function) { return $function($row['x']) < 5; });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE (LENGTH(data.x) < 5)',
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $queries)
    {
        return $queries[0]($table);
    }
}