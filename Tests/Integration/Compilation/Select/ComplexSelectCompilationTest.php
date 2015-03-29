<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation\Select;

use Pinq\Demo\Sql\Tests\Integration\Compilation\SqlAssertsIgnoringWhitespace;
use Pinq\IRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ComplexSelectCompilationTest extends SqlSelectQueryCompilationTest
{
    use SqlAssertsIgnoringWhitespace;

    public function queryTests()
    {
        return [
            [
                'customers',
                function (IRepository $table) {
                    return $table
                        ->where(function ($row) { return $row['age'] <= 50; })
                        ->orderByAscending(function ($row) { return $row['firstName']; })
                        ->thenByAscending(function ($row) { return $row['lastName']; })
                        ->take(50)
                        ->select(function ($row) {
                            return [
                                'fullName'    => $row['firstName'] . ' ' . $row['lastName'],
                                'address'     => $row['address'],
                                'dateOfBirth' => $row['dateOfBirth'],
                            ];
                        });
                },
                <<<SQL
SELECT
CONCAT(CONCAT(customers.firstName,'!! !!'), customers.lastName) AS fullName,
customers.address AS address,
customers.dateOfBirth AS dateOfBirth
FROM (
    SELECT * FROM
    (
        SELECT * FROM
        (SELECT * FROM customers) AS customers
        WHERE (customers.age <= 50)
    ) AS customers
    ORDER BY customers.firstName ASC, customers.lastName ASC
    LIMIT 50 OFFSET 0
) AS customers
SQL
            ],
        ];
    }

    protected function executeTestQueryScope(IRepository $table, array $queries)
    {
        return $queries[0]($table);
    }
}