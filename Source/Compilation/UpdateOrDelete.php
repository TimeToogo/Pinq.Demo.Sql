<?php

namespace Pinq\Demo\Sql\Compilation;

use Pinq\Demo\Sql\Compilation\Compiled\UpdateOrDelete as CompiledUpdateOrDelete;
use Pinq\Demo\Sql\PinqDemoSqlException;
use Pinq\Demo\Sql\Providers\TableSourceInfo;
use Pinq\Expressions as O;
use Pinq\Providers\DSL\Compilation\IOperationCompilation;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UpdateOrDelete extends Query implements IOperationCompilation
{
    /**
     * @var Select
     */
    public $innerSelect;

    public function __construct(
            \PDO $connection,
            TableSourceInfo $table,
            ParameterCollection $parameters
    ) {
        if(!$table->hasPrimaryKeys()) {
            throw new PinqDemoSqlException("Table {$table->getName()} must have primary keys to perform an UPDATE or DELETE query");
        }

        parent::__construct(
                $connection,
                $table,
                $parameters
        );

        $this->innerSelect = new Select($connection, $table, $parameters);
    }

    /**
     * @return CompiledUpdateOrDelete
     */
    public function asCompiled()
    {
        return new CompiledUpdateOrDelete(
            $this->sql,
            $this->parameters->buildRegistry()
        );
    }
}