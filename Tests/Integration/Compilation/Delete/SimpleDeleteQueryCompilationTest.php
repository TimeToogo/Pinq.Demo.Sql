<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Delete;

use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleDeleteQueryCompilationTest extends SqlDeleteQueryCompilationTest
{
    public function queryTests()
    {
        return [
            [
                ['name' => 'customers', 'primary-keys' => ['id']],
                function (IRepository $table) {
                    return $table;
                },
                'DELETE customers FROM customers RIGHT JOIN (SELECT * FROM customers) AS applicable_customers USING (id)'
            ],
            [
                ['name' => 'customers', 'primary-keys' => ['id']],
                function (IRepository $table) {
                    return $table
                        ->where(function ($row) { return $row['age'] <= 50; })
                        ->take(50);
                },
                'DELETE customers FROM customers RIGHT JOIN (SELECT * FROM (SELECT * FROM customers) AS customers WHERE (customers.age <= 50) LIMIT 50 OFFSET 0) AS applicable_customers USING (id)'
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $queries)
    {
        return $queries[0]($table);
    }
}