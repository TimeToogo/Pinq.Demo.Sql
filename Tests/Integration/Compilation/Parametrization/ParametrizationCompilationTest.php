<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Parametrization;

use Pinq\Demo\Sql\Tests\Integration\Compilation\SqlQueryCompilationTest;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ParametrizationCompilationTest extends SqlQueryCompilationTest
{
    const CLASS_CONSTANT_PARAMETER = '!!class-constant!!';

    protected static $staticFieldParameter = '!!static-field!!';

    protected $thisFieldParameter = '!!this-field!!';

    protected function doTestQuery(IRepository $table, array $testData)
    {
        $testData[0]($table)->asArray();
    }
}