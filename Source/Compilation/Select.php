<?php

namespace Pinq\Demo\Sql\Compilation;

use Pinq\Demo\Sql\Compilation\Compiled\Select as CompiledSelect;
use Pinq\Demo\Sql\Providers\TableSourceInfo;
use Pinq\Expressions as O;
use Pinq\Providers\DSL\Compilation\IRequestCompilation;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection;
use Pinq\Queries\Requests\Values;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Select extends Query implements IRequestCompilation
{
    public $retrievalMode = Values::AS_ARRAY;

    public function __construct(
            \PDO $connection,
            TableSourceInfo $table,
            ParameterCollection $parameters
    ) {
        parent::__construct(
                $connection,
                $table,
                $parameters
        );
    }

    /**
     * @return Select
     */
    public function wrapSelectAsDerivedTable()
    {
        $this->sql = "SELECT * FROM ({$this->sql}) AS {$this->tableName}";
    }

    /**
     * @return CompiledSelect
     */
    public function asCompiled()
    {
        return new CompiledSelect(
                $this->sql,
                $this->parameters->buildRegistry(),
                $this->retrievalMode
        );
    }
}