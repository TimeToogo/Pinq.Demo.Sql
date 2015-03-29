<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Update;

use Pinq\Demo\Sql\Tests\Integration\Compilation\SqlAssertsIgnoringWhitespace;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleUpdateQueryCompilationTest extends SqlUpdateQueryCompilationTest
{
    use SqlAssertsIgnoringWhitespace;

    public function queryTests()
    {
        return [
            [
                ['name' => 'customers', 'primary-keys' => ['id']],
                function (IRepository $table) {
                    $table->apply(function (&$row) {
                        $row['x'] = 5;
                    });
                },
                <<<SQL

UPDATE customers RIGHT JOIN (SELECT * FROM customers) AS applicable_customers USING (id)
SET
customers.x = 5
SQL
            ],
            [
                ['name' => 'customers', 'primary-keys' => ['id']],
                function (IRepository $table) {
                    $table->apply(function (&$row) {
                        $row['x'] += $row['y'];
                        $row['z'] -= 50;
                    });
                },
                <<<SQL
UPDATE customers RIGHT JOIN (SELECT * FROM customers) AS applicable_customers USING (id)
SET
customers.x = (customers.x + customers.y),
customers.z = (customers.z - 50)
SQL
            ],
        ];
    }

    protected function doTestQuery(IRepository $table, array $queries)
    {
        $queries[0]($table);
    }
}