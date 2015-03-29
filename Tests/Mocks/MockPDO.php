<?php

namespace Pinq\Demo\Sql\Tests\Mocks;

use PDO;

class MockPDO extends \PDO
{
    /**
     * @var array
     */
    protected $fetchAllQueryResultSet = [];

    /**
     * @var array[]
     */
    protected $statementResults = [];

    /**
     * @var MockPDOStatement[]
     */
    protected $executedStatements = [];

    public function __construct($statementResults = [])
    {

    }

    public static function mockedQuote($value)
    {
        return '\'!!' . $value . '!!\'';
    }

    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        return self::mockedQuote($string);
    }

    public function prepare($statement, $driver_options = null)
    {
       return new MockPDOStatement($this, $statement, $this->fetchAllQueryResultSet);
    }

    /**
     * @return MockPDOStatement[]
     */
    public function getExecutedStatements()
    {
        return $this->executedStatements;
    }

    /**
     * @return MockPDOStatement|null
     */
    public function getLastExecutedStatement()
    {
        return end($this->executedStatements) ?: null;
    }

    /**
     * @param MockPDOStatement $lastExecutedStatement
     */
    public function addExecutedStatement(MockPDOStatement $lastExecutedStatement)
    {
        $this->executedStatements[] = $lastExecutedStatement;
    }

    /**
     * @param array $fetchAllQueryResultSet
     */
    public function setFetchAllQueryResultSet(array $fetchAllQueryResultSet)
    {
        $this->fetchAllQueryResultSet = $fetchAllQueryResultSet;
    }
}