<?php

namespace Pinq\Demo\Sql\Compilation\Compilers;

use Pinq\Demo\Sql;
use Pinq\Demo\Sql\Compilation\UpdateOrDelete;
use Pinq\Demo\Sql\PinqDemoSqlException;
use Pinq\Expressions as O;
use Pinq\Providers\DSL\Compilation\Compilers\OperationQueryCompiler;
use Pinq\Queries;
use Pinq\Queries\Operations;
use Pinq\Queries\Requests;
use Pinq\Queries\Segments;

class OperationCompiler extends OperationQueryCompiler
{
    /**
     * @var UpdateOrDelete
     */
    protected $compilation;

    public function __construct(Queries\IOperationQuery $operationQuery, UpdateOrDelete $query)
    {
        parent::__construct($operationQuery, $query,
            new ScopeCompiler($operationQuery->getScope(), $query->innerSelect));
    }

    public function visitApply(Operations\Apply $operation)
    {
        $function   = $operation->getMutatorFunction();
        $parameters = $function->getParameters();

        // Ensure that calls to apply take the row array by reference
        // because the function will not actually modify the original
        // array if running in PHP.
        if (!$parameters->hasValue() || !$parameters->getValue()->isPassedByReference()) {
            throw new PinqDemoSqlException('Call to ->apply(function): must take the first parameter by reference.');
        }

        $setters = [];
        foreach ($function->getBodyExpressionsUntilReturn() as $expression) {
            // Ensure matches: $row['column'] = ...
            if (!($expression instanceof O\AssignmentExpression)
                || !($expression->getAssignTo() instanceof O\IndexExpression)
                || !($expression->getAssignTo()->getIndex() instanceof O\ValueExpression)
                || !$expression->getAssignTo()->getValue()->equals($parameters->getValue()->asVariable())
            ) {
                throw new PinqDemoSqlException("Call to ->apply(...) must only contain statements in the form \$row['column'] = value, invalid statement found: {$expression->compileDebug()}");
            }

            // Convert any compound assignment operators into expanded form:
            // x += y becomes x = x + y
            $expression = $expression->toBinaryOperationEquivalent();
            $column     = $expression->getAssignTo()->getIndex()->getValue();
            $setterSql  = $this->compilation->expressionCompiler->compile($expression->getAssignmentValue(), $function);

            $setters[$column] = $setterSql;
        }

        $this->compileUpdate($setters);
    }

    protected function compileUpdate(array $setters)
    {
        $tableName = $this->compilation->tableName;

        $this->compilation->sql = "UPDATE {$tableName} RIGHT JOIN ({$this->compilation->innerSelect->sql}) AS applicable_{$tableName} ";
        $this->compilation->sql .= 'USING (' . implode(', ', $this->compilation->table->getPrimaryKeys()) . ')';

        $setterExpressions = [];

        foreach ($setters as $column => $setExpression) {
            $setterExpressions[] = $tableName . '.' . $column . ' = ' . $setExpression;
        }

        $this->compilation->sql .= ' SET ';
        $this->compilation->sql .= implode(', ', $setterExpressions);
    }

    public function visitClear(Operations\Clear $operation)
    {
        $this->compileDelete();
    }

    protected function compileDelete(array $conditions = [])
    {
        $tableName = $this->compilation->tableName;

        $this->compilation->sql = "DELETE {$tableName} FROM {$tableName} RIGHT JOIN ({$this->compilation->innerSelect->sql}) AS applicable_{$tableName} ";
        $this->compilation->sql .= 'USING (' . implode(', ', $this->compilation->table->getPrimaryKeys()) . ')';

        if (!empty($conditions)) {
            $this->compilation->sql .= ' WHERE ';
            $this->compilation->sql .= '(' . implode(' AND ', $conditions) . ')';
        }
    }

    public function visitRemoveWhere(Operations\RemoveWhere $operation)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitAddValues(Operations\AddValues $operation)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitJoinApply(Operations\JoinApply $operation)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitRemoveValues(Operations\RemoveValues $operation)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitUnsetIndex(Operations\UnsetIndex $operation)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitSetIndex(Operations\SetIndex $operation)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }
}
