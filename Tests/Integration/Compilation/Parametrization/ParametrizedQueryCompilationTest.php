<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Parametrization;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParametrizedSqlCompilationTest extends ParametrizationCompilationTest
{
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
                [':p1' => $usedVariableParameter]
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
                [':p1' => $this->thisFieldParameter]
            ],
            [
                'data',
                function (IRepository $table) {
                    return $table
                        ->orderByAscending(function ($row) { return self::$staticFieldParameter . $row['x']; });
                },
                'SELECT * FROM (SELECT * FROM data) AS data ORDER BY CONCAT(:p1, data.x) ASC',
                // Bindings
                [':p1' => self::$staticFieldParameter]
            ],
            [
                'data',
                function (IRepository $table) {
                    return $table
                        ->where(function ($row) { return static::CLASS_CONSTANT_PARAMETER > $row['x']; });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE (:p1 > data.x)',
                // Bindings
                [':p1' => static::CLASS_CONSTANT_PARAMETER]
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
                [':p1' => static::CLASS_CONSTANT_PARAMETER, ':p2' => $usedVariableParameter, ':p3' => $this->thisFieldParameter]
            ],
        ];
    }
}