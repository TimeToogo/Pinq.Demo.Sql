<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation;

use Pinq\Demo\Sql\Compilation\Compilers\ExpressionCompiler;
use Pinq\Demo\Sql\Compilation\Select;
use Pinq\Demo\Sql\Providers\TableSourceInfo;
use Pinq\Demo\Sql\Tests\MockedPdoDemoSqlTestCase;
use Pinq\Parsing\FunctionInterpreter;
use Pinq\Parsing\FunctionReflection;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExpressionCompilationTest extends MockedPdoDemoSqlTestCase
{
    public function expressions()
    {
        return [
            [
                function () { 1; },
                '1'
            ],
            [
                function () { 1.0; },
                '1'
            ],
            [
                function () { 4454.6556; },
                '4454.6556'
            ],
            [
                function () { 5 + 1 * 2 / 4; },
                '(5 + ((1 * 2) / 4))'
            ],
            [
                function () { 'some string'; },
                "'!!some string!!'"
            ],
            // Constants are treated as parameters as they are not truly constant
            [
                function () { SOME_CONSTANT; },
                ':p1'
            ],
            [
                function () { SomeClass::SOME_CONSTANT; },
                ':p1'
            ],
            [
                function () { SomeClass::$abc; },
                ':p1'
            ],
            [
                function () { $abc; },
                ':p1'
            ],
            [
                function () { $this->foo; },
                ':p1'
            ],
            [
                function ($row) { $row['x'] >= 1; },
                '(table.x >= 1)'
            ],
            [
                function () { 1 && 1 || 1; },
                '((1 AND 1) OR 1)'
            ],
            [
                function () { !true; },
                '(NOT :p1)'
            ],
            [
                function () { +true; },
                '(+:p1)'
            ],
            [
                function () { ~555; },
                '(~555)'
            ],
            [
                function () { 1 ? 5 : -5; },
                'CASE WHEN 1 THEN 5 ELSE (-5) END'
            ],
            [
                function () { strlen(123); },
                'LENGTH(123)'
            ],
            [
                function () { isset($var); },
                '(:p1 IS NOT NULL)'
            ],
            [
                function () { isset($var, $var); },
                '(:p1 IS NOT NULL AND :p2 IS NOT NULL)'
            ],
            [
                function () { empty($var); },
                '(NOT :p1)'
            ],
            [
                function () { 123 == 123; },
                '(123 <=> 123)'
            ],
            [
                function () { 123 != 123; },
                '(NOT (123 <=> 123))'
            ],
            [
                function () { 'foo' . $var; },
                "CONCAT('!!foo!!', :p1)"
            ],
            [
                function () { 1 . 2 . 3 . 4; },
                'CONCAT(CONCAT(CONCAT(1, 2), 3), 4)'
            ],
        ];
    }

    /**
     * @dataProvider expressions
     */
    public function testExpressionCompilation($phpCallable, $expectedSql)
    {
        // Parse the first statement from callable into an expression tree.
        $expression = FunctionInterpreter::getDefault()
                          ->getStructure(FunctionReflection::fromCallable($phpCallable))
                          ->getBodyExpressions()[0];

        $compiler = new ExpressionCompiler(
            new Select(
                $this->pdo,
                new TableSourceInfo('table'),
                new ParameterCollection()
            )
        );

        $this->assertSame($expectedSql, $compiler->compile($expression), 'PHP Code: ' . $expression->compile());
    }
}