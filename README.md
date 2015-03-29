Pinq.Demo.Sql
=============

This is a demo of using the query provider functionality of PINQ.
This library is based on [PINQ V3](https://github.com/TimeToogo/Pinq)
and demonstrates the power of the DSL query provider API.
**This is a proof of concept and should not be run in production.**

Overview
========

This demo written in under 2 KLOC, implements a small section of the PINQ query API mapping
it to a MySQL database backend. Tables are treated as collections and rows are represented
as associative arrays.

```php
use Pinq\Demo\Sql\DB;

$db = new DB($pdo);

$db->table('customers')
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
    })
    ->asArray()
```

The above query will be converted to a SQL equivalent:

```sql
SELECT
CONCAT(CONCAT(customers.firstName, ' '), customers.lastName) AS fullName,
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
```

As this is just a demo the SQL generation is fairly makeshift and does
not produce the most efficient code possible.

[More examples can be found in the test suite](Tests/Integration)

API Support
===========

**Scope (filtering) API**

 - `->where(function)` = `WHERE <expr>`
 - `->orderBy(function, direction)`, `->orderByAscending(function)`, `->orderByDescending(function)` = `ORDER BY <expr> ASC|DESC`
 - `->unique()` = `DISTINCT`
 - `->take(amount)`, `->skip(amount)`, `->slice(skip, take)` = `LIMIT <amount> OFFSET <amount>`
 - `->select(function)` = `SELECT <expr> AS <alias>...`

**Request (data retrieval) API**

 - `->asArray()`, `->getIterator()`, `->getTrueIterator()`, `->asTraversable()`, `->asCollection()` = `SELECT * FROM`

**Operation (data mutation) API**

 - `->apply(function)` = `UPDATE <table> SET <column> = <expr>...`
 - `->clear()` = `DELETE FROM <table>`

**PHP Expression Syntax**

 - Binary operators: `+, -, *, /, %, ==, !=, &&, ||, >, >=, <, <=, .`
 - Unary operators: `!, ~, -, +`
 - `isset` and `empty`
 - Ternary: `?:`
 - Functions: `strlen, md5`

Documentation
=============

This repository as an example of how the PINQ query provider can be implemented.
For more details you can browse the repository or follow the links below.

 - [High Level API](Source/README.md)
 - [Query Providers](Source/Providers/README.md)
 - [Compilation Structure](Source/Compilation/README.md)
    - [Preprocessors](Source/Compilation/Preprocessors/README.md)
    - [Compilers](Source/Compilation/Compilers/README.md)
    - [Compiled Queries](Source/Compilation/Preprocessors/README.md)
