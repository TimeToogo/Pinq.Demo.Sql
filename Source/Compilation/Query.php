<?php

namespace Pinq\Demo\Sql\Compilation;

use PDO;
use Pinq\Demo\Sql\Compilation\Compilers\ExpressionCompiler;
use Pinq\Demo\Sql\Providers\TableSourceInfo;
use Pinq\Expressions as O;
use Pinq\Expressions\Operators;
use Pinq\Providers\DSL\Compilation;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterHasher;
use Pinq\Providers\DSL\Compilation\QueryCompilation;
use Pinq\Queries\Functions\FunctionBase;

abstract class Query extends QueryCompilation
{
    /**
     * @var TableSourceInfo
     */
    public $table;

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    public $sql = '';

    /**
     * @var PDO
     */
    public $connection;

    /**
     * @var ExpressionCompiler
     */
    public $expressionCompiler;

    protected $parameterIndex = 1;

    public function __construct(
            PDO $connection,
            TableSourceInfo $table,
            ParameterCollection $parameters
    ) {
        parent::__construct($parameters);
        $this->table              = $table;
        $this->tableName          = $table->getName();
        $this->connection         = $connection;
        $this->expressionCompiler = new ExpressionCompiler($this);
    }

    public function addExpressionParameter(O\Expression $expression, FunctionBase $function = null)
    {
        $namedParameter = $this->createNamedParameter();
        $this->parameters->addExpression(
                $expression,
                ParameterHasher::valueType(),
                $function,
                $namedParameter
        );

        return $namedParameter;
    }

    public function addIdParameter($id)
    {
        $namedParameter = $this->createNamedParameter();
        $this->parameters->addId(
                $id,
                ParameterHasher::valueType(),
                $namedParameter
        );


        return $namedParameter;
    }

    protected function createNamedParameter()
    {
        return ':p' . $this->parameterIndex++;
    }
}
