<?php

namespace Pinq\Demo\Sql\Tests\Mocks;

use PDO;
use Traversable;

class MockPDOStatement extends \PDOStatement implements \IteratorAggregate
{
    /**
     * @var string
     */
    public $sql;

    /**
     * @var array
     */
    public $bindings = [];

    /**
     * @var MockPDO
     */
    protected $mockPDO;

    /**
     * @var array
     */
    protected $fetchAllResultSet;

    public function __construct(MockPDO $mockPDO, $sql, array $fetchAllResultSet = [])
    {
        $this->mockPDO           = $mockPDO;
        $this->sql               = $sql;
        $this->fetchAllResultSet = $fetchAllResultSet;
    }

    public function bindParam(
        $parameter,
        &$variable,
        $data_type = PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
        $this->bindings[$parameter] = $variable;
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $this->bindings[$parameter] = $value;
    }

    public function execute($input_parameters = null)
    {
        $this->bindings = ($input_parameters ?: []) + $this->bindings;

        $this->mockPDO->addExecutedStatement($this);
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null)
    {
        return $this->fetchAllResultSet;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->fetchAllResultSet);
    }
}