<?php

namespace Pinq\Demo\Sql\Tests;

use Pinq\Demo\Sql\DB;
use Pinq\Demo\Sql\Tests\Mocks\MockPDO;

/**
 * The base class for test cases that do not require access
 * to the database
 */
abstract class MockedPdoDemoSqlTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockPDO
     */
    protected $pdo;

    /**
     * @var DB
     */
    protected $db;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->pdo = new MockPDO();
        $this->db = new DB($this->pdo);
    }
}

?>