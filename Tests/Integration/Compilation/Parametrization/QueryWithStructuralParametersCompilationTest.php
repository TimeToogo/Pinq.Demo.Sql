<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Parametrization;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class QueryWithStructuralParametersCompilationTest extends ParametrizationCompilationTest
{
    public function queryTests()
    {
        $function = 'strlen';
        $otherFunction = 'md5';

        return [
            [
                'data',
                function (IRepository $table) use ($function) {
                    return $table
                        ->where(function ($row) use ($function) {
                            return $function($row['x']);
                        });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE LENGTH(data.x)'
            ],
            [
                'data',
                function (IRepository $table) use ($otherFunction) {
                    return $table
                        ->where(function ($row) use ($otherFunction) {
                            return $otherFunction($row['x']);
                        });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE MD5(data.x)'
            ],
            [
                'data',
                function (IRepository $table) use ($function, $otherFunction) {
                    return $table
                        ->where(function ($row) use ($function, $otherFunction) {
                            return $function($row['y']) == $otherFunction($row['x']);
                        });
                },
                'SELECT * FROM (SELECT * FROM data) AS data WHERE (LENGTH(data.y) <=> MD5(data.x))'
            ],
        ];
    }
}