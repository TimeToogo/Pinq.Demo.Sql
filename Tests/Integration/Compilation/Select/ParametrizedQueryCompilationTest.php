<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParametrizedSqlCompilationTest extends SqlSelectQueryCompilationTest
{
    const CLASS_CONSTANT_PARAMETER = '!!class-constant!!';

    private static $staticFieldParameter = '!!static-field!!';

    private $thisFieldParameter = '!!this-field!!';

    public function queryTests()
    {
        $usedVariableParameter = '!!used-variable!!';

        return [
            [
                'data',
                function (IRepository $table) use ($usedVariableParameter) {
                    return $table
                        ->where(function ($row) use ($usedVariableParameter) { return $row['x'] <= $usedVariableParameter; });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE (data.x <= :p1)',
                // Bindings
                [':p1' => '!!used-variable!!']
            ],
            [
                'data',
                function (IRepository $table) {
                    return $table
                        ->select(function ($row) {
                            return [
                                'y' => $row['x'] == $this->thisFieldParameter
                            ];
                        });
                },
                'SELECT (data.x <=> :p1) AS y FROM (SELECT * FROM data) AS data',
                // Bindings
                [':p1' => '!!this-field!!']
            ],
            [
                'data',
                function (IRepository $table) {
                    return $table
                        ->orderByAscending(function ($row) { return self::$staticFieldParameter . $row['x']; });
                },
                'SELECT * FROM (SELECT * FROM data) AS data ORDER BY CONCAT(:p1, data.x) ASC',
                // Bindings
                [':p1' => '!!static-field!!']
            ],
            [
                'data',
                function (IRepository $table) {
                    return $table
                        ->where(function ($row) { return static::CLASS_CONSTANT_PARAMETER > $row['x']; });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE (:p1 > data.x)',
                // Bindings
                [':p1' => '!!class-constant!!']
            ],
            [
                'data',
                function (IRepository $table) use ($usedVariableParameter) {
                    return $table
                        ->where(function ($row) use ($usedVariableParameter) {
                            return (static::CLASS_CONSTANT_PARAMETER == $usedVariableParameter) == $this->thisFieldParameter;
                        });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE ((:p1 <=> :p2) <=> :p3)',
                // Bindings
                [':p1' => '!!class-constant!!', ':p2' => '!!used-variable!!', ':p3' => '!!this-field!!']
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $queries)
    {
        return $queries[0]($table);
    }
}