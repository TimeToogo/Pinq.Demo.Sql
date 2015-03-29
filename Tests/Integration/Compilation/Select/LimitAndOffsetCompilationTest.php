<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LimitAndOffsetCompilationTest extends SqlSelectQueryCompilationTest
{
    public function queryTests()
    {
        return [
            [
                'numbers',
                ['limit' => 1],
                'SELECT * FROM numbers LIMIT 1 OFFSET 0'
            ],
            [
                'numbers',
                ['offset' => 1],
                'SELECT * FROM numbers LIMIT 18446744073709551615 OFFSET 1'
            ],
            [
                'numbers',
                ['limit' => 1, 'offset' => 5],
                'SELECT * FROM numbers LIMIT 1 OFFSET 5'
            ],
            [
                'numbers',
                ['limit' => 'abc', 'offset' => 'xyz'],
                "SELECT * FROM numbers LIMIT '!!abc!!' OFFSET '!!xyz!!'"
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $slices)
    {
        $table = $table->slice(isset($slices['offset']) ? $slices['offset'] : 0, isset($slices['limit']) ? $slices['limit'] : null);

        return $table;
    }
}