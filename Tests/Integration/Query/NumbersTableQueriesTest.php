<?php

namespace Pinq\Demo\Sql\Tests\Integration\Query;

use Pinq\ICollection;
use Pinq\IQueryable;
use Pinq\IRepository;
use Pinq\ITraversable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NumbersTableQueriesTest extends DataSetSqlQueryTest
{
    public static function setUpBeforeClass()
    {
        self::getPDO()->query('CREATE TABLE IF NOT EXISTS numbers (x INT NOT NULL)');
    }

    public static function tearDownAfterClass()
    {
        self::getPDO()->query('DROP TABLE numbers');
    }

    protected function getDataSetArray()
    {
        return [
            'numbers' => [
                ['x' => 1],
                ['x' => 2],
                ['x' => 3],
                ['x' => 4],
                ['x' => 5],
                ['x' => 6],
                ['x' => 7],
                ['x' => 8],
                ['x' => 9],
                ['x' => 10],
            ]
        ];
    }

    public function testGetAllResults()
    {
        $results = [
            'asArray'         => $this->db->table('numbers')->asArray(),
            'getIterator'     => $this->db->table('numbers')->getIterator(),
            'getTrueIterator' => $this->db->table('numbers')->getTrueIterator(),
            '-'               => $this->db->table('numbers'),
            'asTraversable'   => $this->db->table('numbers')->asTraversable(),
            'asCollection'    => $this->db->table('numbers')->asCollection(),
        ];

        foreach($results as $method => $result) {
            $this->assertEquivalentResultSets(
                $this->getDataSetArray()['numbers'],
                is_array($result) ? $result : iterator_to_array($result, true),
                "->{$method}()"
            );
        }

        $this->assertInternalType('array', $results['asArray']);

        $this->assertInstanceOf('Traversable', $results['getIterator']);

        $this->assertInstanceOf('Traversable', $results['getTrueIterator']);

        $this->assertInstanceOf(ITraversable::ITRAVERSABLE_TYPE, $results['asTraversable']);
        $this->assertNotInstanceOf(IQueryable::IQUERYABLE_TYPE, $results['asTraversable']);

        $this->assertInstanceOf(ICollection::ICOLLECTION_TYPE, $results['asCollection']);
        $this->assertNotInstanceOf(IRepository::IREPOSITORY_TYPE, $results['asCollection']);
    }

    public function testFilterCondition()
    {
        $this->assertEquivalentResultSets([
            ['x' => 1],
            ['x' => 2],
            ['x' => 3],
            ['x' => 4],
            ['x' => 5],
        ],
            $this->db->table('numbers')
                ->where(function ($row) { return $row['x'] <= 5; })
                ->asArray()
        );
    }

    public function testOrderByDescending()
    {
        $reveredRows = array_reverse($this->getDataSetArray()['numbers']);

        $this->assertEquivalentResultSets(
            $reveredRows,
            $this->db->table('numbers')
                ->orderByDescending(function ($row) { return $row['x']; })
                ->asArray()
        );

        $this->assertEquivalentResultSets(
            $reveredRows,
            $this->db->table('numbers')
                ->orderByAscending(function ($row) { return -$row['x']; })
                ->asArray()
        );
    }

    public function testSelect()
    {
        $this->assertEquivalentResultSets([
            ['x' => 2, 'y' => '1-foo'],
            ['x' => 4, 'y' => '2-foo'],
            ['x' => 6, 'y' => '3-foo'],
            ['x' => 8, 'y' => '4-foo'],
            ['x' => 10, 'y' => '5-foo'],
            ['x' => 12, 'y' => '6-foo'],
            ['x' => 14, 'y' => '7-foo'],
            ['x' => 16, 'y' => '8-foo'],
            ['x' => 18, 'y' => '9-foo'],
            ['x' => 20, 'y' => '10-foo'],
        ],
            $this->db->table('numbers')
                ->select(function ($row) {
                    return [
                        'x' => $row['x'] * 2,
                        'y' => $row['x'] . '-foo',
                    ];
                })
                ->asArray()
        );
    }

    public function testTake()
    {
        $this->assertEquivalentResultSets([
            ['x' => 1],
            ['x' => 2],
        ],
            $this->db->table('numbers')
                ->take(2)
                ->asArray()
        );

        $this->assertEquivalentResultSets([
            ['x' => 1],
            ['x' => 2],
            ['x' => 3],
            ['x' => 4],
            ['x' => 5],
        ],
            $this->db->table('numbers')
                ->take(5)
                ->asArray()
        );
    }

    public function testSkip()
    {
        $this->assertEquivalentResultSets([
            ['x' => 3],
            ['x' => 4],
            ['x' => 5],
            ['x' => 6],
            ['x' => 7],
            ['x' => 8],
            ['x' => 9],
            ['x' => 10],
        ],
            $this->db->table('numbers')
                ->skip(2)
                ->asArray()
        );

        $this->assertEquivalentResultSets([
            ['x' => 6],
            ['x' => 7],
            ['x' => 8],
            ['x' => 9],
            ['x' => 10],
        ],
            $this->db->table('numbers')
                ->skip(5)
                ->asArray()
        );
    }

    public function testSlice()
    {
        $this->assertEquivalentResultSets([
            ['x' => 2],
            ['x' => 3],
        ],
            $this->db->table('numbers')
                ->slice(1, 2)
                ->asArray()
        );

        $this->assertEquivalentResultSets([
            ['x' => 4],
            ['x' => 5],
            ['x' => 6],
            ['x' => 7],
            ['x' => 8],
        ],
            $this->db->table('numbers')
                ->slice(3, 5)
                ->asArray()
        );
    }

    public function testUnique()
    {
        $this->assertEquivalentResultSets(
            $this->getDataSetArray()['numbers'],
            $this->db->table('numbers')
                ->unique()
                ->asArray()
        );
    }

    public function testFunctionCall()
    {
        $hashedNumbers = [];

        foreach($this->getDataSetArray()['numbers'] as $row) {
            $hashedNumbers[] = ['x' => md5($row['x'])];
        }

        $this->assertEquivalentResultSets(
            $hashedNumbers,
            $this->db->table('numbers')
                ->select(function ($row) {
                    return [
                        'x' => md5($row['x']),
                    ];
                })
                ->asArray()
        );
    }

    public function testSelectUnnaturalOrderOfSqlOperations()
    {
        $this->assertEquivalentResultSets([
            ['x' => 5],
            ['x' => 6],
            ['x' => 9],
        ],
            $this->db->table('numbers')
                ->where(function ($row) { return $row['x'] >= 2 && $row['x'] <= 6 || $row['x'] == 9; }) // 2,3,4,5,6,9
                ->orderByDescending(function ($row) { return $row['x']; })                              // 9,6,5,4,3,2
                ->take(5)                                                                               // 9,6,5,4,3
                ->where(function ($row) { return $row['x'] != 4; })                                     // 9,6,5,3
                ->orderByAscending(function ($row) { return $row['x']; })                               // 3,5,6,9
                ->skip(1)                                                                               // 5,6,9
                ->asArray()
        );
    }

    protected function assertNumbersTableContains(array $rows)
    {
        $data = $this->getPDO()->query('SELECT * FROM numbers')->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquivalentResultSets($rows, $data);
    }

    public function testClearAll()
    {
        $this->db->table('numbers', ['x'])
            ->clear();

        $this->assertNumbersTableContains([]);
    }

    public function testClearFiltered()
    {
        $this->db->table('numbers', ['x'])
            ->where(function ($row) { return $row['x'] >= 4 && $row['x'] <= 7 || $row['x'] == 2; })
            ->clear();

        $this->assertNumbersTableContains([
            ['x' => 1],
            ['x' => 3],
            ['x' => 8],
            ['x' => 9],
            ['x' => 10],
        ]);
    }

    public function testClearUnnaturalOrderOfSqlOperations()
    {
        $this->db->table('numbers', ['x'])
            ->where(function ($row) { return $row['x'] >= 2 && $row['x'] <= 6 || $row['x'] == 9; }) // 2,3,4,5,6,9
            ->orderByDescending(function ($row) { return $row['x']; })                              // 9,6,5,4,3,2
            ->take(5)                                                                               // 9,6,5,4,3
            ->where(function ($row) { return $row['x'] != 4; })                                     // 9,6,5,3
            ->orderByAscending(function ($row) { return $row['x']; })                               // 3,5,6,9
            ->skip(1)                                                                               // 5,6,9
            ->clear();

        $this->assertNumbersTableContains([
            ['x' => 1],
            ['x' => 2],
            ['x' => 3],
            ['x' => 4],
            ['x' => 7],
            ['x' => 8],
            ['x' => 10],
        ]);
    }

    public function testUpdateAll()
    {
        $this->db->table('numbers', ['x'])
            ->apply(function (&$row) {
                $row['x'] *= 2;
            });

        $this->assertNumbersTableContains([
            ['x' => 2],
            ['x' => 4],
            ['x' => 6],
            ['x' => 8],
            ['x' => 10],
            ['x' => 12],
            ['x' => 14],
            ['x' => 16],
            ['x' => 18],
            ['x' => 20],
        ]);
    }

    public function testUpdateFiltered()
    {
        $this->db->table('numbers', ['x'])
            ->where(function ($row) { return $row['x'] > 1 && $row['x'] <= 4; })
            ->apply(function (&$row) {
                $row['x'] += 5;
            });

        $this->assertNumbersTableContains([
            ['x' => 1],
            ['x' => 7],
            ['x' => 8],
            ['x' => 9],
            ['x' => 5],
            ['x' => 6],
            ['x' => 7],
            ['x' => 8],
            ['x' => 9],
            ['x' => 10],
        ]);
    }

    public function testUpdateUnnaturalOrderOfSqlOperations()
    {
        $this->db->table('numbers', ['x'])
            ->where(function ($row) { return $row['x'] >= 2 && $row['x'] <= 6 || $row['x'] == 9; }) // 2,3,4,5,6,9
            ->orderByDescending(function ($row) { return $row['x']; })                              // 9,6,5,4,3,2
            ->take(5)                                                                               // 9,6,5,4,3
            ->where(function ($row) { return $row['x'] != 4; })                                     // 9,6,5,3
            ->orderByAscending(function ($row) { return $row['x']; })                               // 3,5,6,9
            ->skip(1)                                                                               // 5,6,9
            ->apply(function (&$row) {
                $row['x'] *= $row['x'];
            });

        $this->assertNumbersTableContains([
            ['x' => 1],
            ['x' => 2],
            ['x' => 3],
            ['x' => 4],
            ['x' => 25],
            ['x' => 36],
            ['x' => 7],
            ['x' => 8],
            ['x' => 81],
            ['x' => 10],
        ]);
    }
}