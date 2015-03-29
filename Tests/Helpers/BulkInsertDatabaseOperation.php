<?php

namespace Pinq\Demo\Sql\Tests\Helpers;

use PDOStatement;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DataSet_ITable;
use PHPUnit_Extensions_Database_DataSet_ITableMetaData;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Exception;
use PHPUnit_Extensions_Database_Operation_IDatabaseOperation;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BulkInsertDatabaseOperation implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{

    /**
     * Executes the database operation against the given $connection for the
     * given $dataSet.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     * @throws PHPUnit_Extensions_Database_Operation_Exception
     */
    public function execute(
        PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection,
        PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
    ) {
        foreach ($dataSet->getIterator() as $table) {
            /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */

            $rowCount = $table->getRowCount();
            if($rowCount === 0) {
                continue;
            }

            $databaseTableMetaData = $dataSet->getTableMetaData($table->getTableMetaData()->getTableName());
            $bulkInsert = $this->buildBulkInsertQuery($connection, $databaseTableMetaData, $rowCount);
            $statement = $connection->getConnection()->prepare($bulkInsert);
            $this->bindInsertParameters($rowCount, $table, $databaseTableMetaData, $statement);

            try {
                $statement->execute();
            }
            catch (\Exception $e) {
                throw new PHPUnit_Extensions_Database_Operation_Exception(
                    'BULK_INSERT', $bulkInsert, null, $table, $e->getMessage()
                );
            }
        }
    }

    /**
     * Builds a bulk INSERT query for the supplied amount of rows.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData
     * @param int $rowCount
     * @return string
     */
    protected function buildBulkInsertQuery(
        PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection,
        PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
        $rowCount
    ) {
        $insert = 'INSERT INTO' . $connection->quoteSchemaObject($databaseTableMetaData->getTableName()) . ' ';
        $quotedColumns = array_map([$connection, 'quoteSchemaObject'], $databaseTableMetaData->getColumns());
        $insert .= '(' . implode(', ', $quotedColumns) . ')';
        $insert .= ' VALUES ';

        $rowParameters = '(' . implode(',', array_fill(0, count($databaseTableMetaData->getColumns()), '?')) . ')';

        $insert .= implode(',', array_fill(0, $rowCount, $rowParameters));

        return $insert;
    }

    /**
     * Binds all the insert parameters to the supplied query.
     *
     * @param int $rowCount
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData
     * @param PDOStatement $statement
     * @return void
     */
    protected function bindInsertParameters(
        $rowCount,
        PHPUnit_Extensions_Database_DataSet_ITable $table,
        PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
        PDOStatement $statement)
    {
        $parameterIndex = 1;

        for ($i = 0; $i < $rowCount; $i++) {
            $row = $table->getRow($i);

            foreach ($databaseTableMetaData->getColumns() as $column) {
                $statement->bindValue($parameterIndex++, isset($row[$column]) ? $row[$column] : null);
            }
        }
    }
}