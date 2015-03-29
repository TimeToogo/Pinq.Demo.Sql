<?php

namespace Pinq\Demo\Sql\Tests\Integration\Query;

use PHPUnit_Extensions_Database_DataSet_IDataSet;
use Pinq\Demo\Sql\Tests\Helpers\ArrayDataSet;
use Pinq\Demo\Sql\Tests\Helpers\BulkInsertDatabaseOperation;
use Pinq\Demo\Sql\Tests\RealPdoSqlTestCase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DataSetSqlQueryTest extends RealPdoSqlTestCase
{
    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new ArrayDataSet($this->getDataSetArray());
    }

    protected function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Composite(array(
            \PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE(),
            new BulkInsertDatabaseOperation()
        ));
    }

    /**
     * @return array[]
     */
    protected abstract function getDataSetArray();

    protected function assertEquivalentResultSets(array $expected, array $actual, $message = '')
    {
        $this->assertSame($expected, $actual, $message);
    }
}