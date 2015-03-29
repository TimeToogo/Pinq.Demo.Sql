<?php

namespace Pinq\Demo\Sql\Tests;

use Pinq\Demo\Sql\DB;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;

/**
 * The base class for test cases that required a real database
 */
abstract class RealPdoSqlTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var \PDO
     */
    private static $pdo;

    /**
     * @var \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    private $connection;

    /**
     * @var DB
     */
    protected $db;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->db = new DB(self::getPDO());
    }

    protected static function getPDO()
    {
        if (self::$pdo == null) {
            self::$pdo = new \PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$pdo;
    }

    protected function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->createDefaultDBConnection(self::getPDO(), $GLOBALS['DB_DBNAME']);
        }

        return $this->connection;
    }
}

?>