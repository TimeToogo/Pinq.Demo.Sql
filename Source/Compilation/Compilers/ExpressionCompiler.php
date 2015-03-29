<?php

namespace Pinq\Demo\Sql\Compilation\Compilers;

use Pinq\Demo\Sql\Compilation\Query;
use Pinq\Demo\Sql\PinqDemoSqlException;
use Pinq\Expressions as O;
use Pinq\Expressions\ArgumentExpression;
use Pinq\Expressions\Operators;
use Pinq\Expressions\UnaryOperationExpression;
use Pinq\Queries\Functions\FunctionBase;
use Pinq\Queries\Functions\ProjectionBase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExpressionCompiler extends O\ExpressionVisitor
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var FunctionBase
     */
    protected $function;

    /**
     * @var string
     */
    protected $sql = '';

    /**
     * @var array
     */
    protected $binaryOperators = [
        Operators\Binary::ADDITION                 => '+',
        Operators\Binary::SUBTRACTION              => '-',
        Operators\Binary::MULTIPLICATION           => '*',
        Operators\Binary::DIVISION                 => '/',
        Operators\Binary::MODULUS                  => '%',
        Operators\Binary::EQUALITY                 => '<=>',
        Operators\Binary::GREATER_THAN             => '>',
        Operators\Binary::GREATER_THAN_OR_EQUAL_TO => '>=',
        Operators\Binary::LESS_THAN                => '<',
        Operators\Binary::LESS_THAN_OR_EQUAL_TO    => '<=',
        Operators\Binary::LOGICAL_OR               => 'OR',
        Operators\Binary::LOGICAL_AND              => 'AND',
    ];

    /**
     * @var array
     */
    protected $unaryOperators = [
        Operators\Unary::NOT         => 'NOT ',
        Operators\Unary::BITWISE_NOT => '~',
        Operators\Unary::NEGATION    => '-',
        Operators\Unary::PLUS        => '+',
    ];

    /**
     * @var array
     */
    protected $functions = [
        'strlen' => 'LENGTH',
        'md5'    => 'MD5',
    ];

    /**
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Compiles the supplied expression tree to equivalent SQL.
     * Any parameters will be added to the query and with the supplied function context.
     *
     * @param O\Expression $expression
     * @param FunctionBase $functionContext
     *
     * @return string
     */
    public function compile(O\Expression $expression, FunctionBase $functionContext = null)
    {
        $previous = [$this->sql, $this->function];

        $this->sql = '';
        $this->function = $functionContext;

        $this->walk($expression);
        $sql = $this->sql;

        list($this->sql, $this->function) = $previous;

        return $sql;
    }

    protected function compileInContext(O\Expression $expression)
    {
        return $this->compile($expression, $this->function);
    }

    public function compileReturn(ProjectionBase $projectionFunction)
    {
        $returnExpression = $projectionFunction->getReturnValueExpression() ?: O\Expression::value(null);

        return $this->compile(
            $returnExpression,
            $projectionFunction
        );
    }

    protected function addParameter(O\Expression $expression)
    {
        $this->sql .= $this->query->addExpressionParameter($expression, $this->function);
    }

    protected function visitBinaryOperation(O\BinaryOperationExpression $expression)
    {
        $operator = $expression->getOperator();

        switch ($operator) {
            case Operators\Binary::INEQUALITY:
                $this->walk(O\Expression::unaryOperation(
                    Operators\Unary::NOT,
                    $expression->update(
                        $expression->getLeftOperand(),
                        Operators\Binary::EQUALITY,
                        $expression->getRightOperand()
                    )
                ));
                return;

            case Operators\Binary::CONCATENATION:
                $this->sql .= 'CONCAT(';
                $this->walk($expression->getLeftOperand());
                $this->sql .= ', ';
                $this->walk($expression->getRightOperand());
                $this->sql .= ')';
                return;
        }

        if (!isset($this->binaryOperators[$operator])) {
            throw new PinqDemoSqlException("Binary operator not supported: {$operator}");
        }

        $this->sql .= '(';
        $this->walk($expression->getLeftOperand());
        $this->sql .= ' ';
        $this->sql .= $this->binaryOperators[$operator];
        $this->sql .= ' ';
        $this->walk($expression->getRightOperand());
        $this->sql .= ')';
    }

    protected function visitUnaryOperation(UnaryOperationExpression $expression)
    {
        $operator = $expression->getOperator();

        if (!isset($this->unaryOperators[$operator])) {
            throw new PinqDemoSqlException("Unary operator not supported: {$operator}");
        }

        $this->sql .= '(';
        $this->sql .= $this->unaryOperators[$operator];
        $this->walk($expression->getOperand());
        $this->sql .= ')';
    }

    protected function visitConstant(O\ConstantExpression $expression)
    {
        $this->addParameter($expression);
    }

    protected function visitClassConstant(O\ClassConstantExpression $expression)
    {
        $this->addParameter($expression);
    }

    protected function visitTernary(O\TernaryExpression $expression)
    {
        $this->sql .= 'CASE WHEN ';
        $this->walk($expression->getCondition());
        $this->sql .= ' THEN ';
        $this->walk($expression->hasIfTrue() ? $expression->getIfTrue() : $expression->getCondition());
        $this->sql .= ' ELSE ';
        $this->walk($expression->getIfFalse());
        $this->sql .= ' END';
    }

    protected function visitIndex(O\IndexExpression $expression)
    {
        $valueExpression = $expression->getValue();
        if (!($valueExpression instanceof O\VariableExpression)
            || !($valueExpression->getName() instanceof O\ValueExpression)
        ) {
            throw new PinqDemoSqlException('Variable indexer is not supported');
        }

        $index = $expression->getIndex()->getValue();
        $this->sql .= $this->query->tableName . '.' . $index;
    }

    protected function visitFunctionCall(O\FunctionCallExpression $expression)
    {
        $functionName = $expression->getName()->getValue();

        if (!isset($this->functions[$functionName])) {
            throw new PinqDemoSqlException("Function not supported: {$functionName}");
        }

        $arguments = [];
        foreach ($expression->getArguments() as $argument) {
            $arguments[] = $this->compileInContext($argument);
        }

        $this->sql .= $this->functions[$functionName] . '(' . implode(', ', $arguments) . ')';
    }

    protected function visitArgument(ArgumentExpression $expression)
    {
        if ($expression->isUnpacked()) {
            throw new PinqDemoSqlException('Does not support argument unpacking');
        }

        $this->walk($expression->getValue());
    }

    protected function visitValue(O\ValueExpression $expression)
    {
        $value = $expression->getValue();

        if (is_bool($value)) {
            $this->sql .= $value ? 'TRUE' : 'FALSE';
        } elseif (is_int($value) || is_float($value)) {
            $this->sql .= $value;
        } else {
            $this->sql .= $this->query->connection->quote($value);
        }
    }

    protected function visitField(O\FieldExpression $expression)
    {
        $this->addParameter($expression);
    }

    protected function visitStaticField(O\StaticFieldExpression $expression)
    {
        $this->addParameter($expression);
    }

    protected function visitVariable(O\VariableExpression $expression)
    {
        $this->addParameter($expression);
    }

    protected function visitIsset(O\IssetExpression $expression)
    {
        $binaryOperations = [];
        foreach ($expression->getValues() as $value) {
            $binaryOperations[] = $this->compileInContext($value) . ' IS NOT NULL';
        }

        $this->sql .= '(' . implode(' AND ', $binaryOperations) . ')';
    }

    protected function visitEmpty(O\EmptyExpression $expression)
    {
        $this->walk(O\Expression::unaryOperation(Operators\Unary::NOT, $expression->getValue()));
    }

    protected function visitInvocation(O\InvocationExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitMethodCall(O\MethodCallExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitStaticMethodCall(O\StaticMethodCallExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitArray(O\ArrayExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitArrayItem(O\ArrayItemExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitAssignment(O\AssignmentExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitCast(O\CastExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitClosure(O\ClosureExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitClosureUsedVariable(O\ClosureUsedVariableExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitParameter(O\ParameterExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitUnset(O\UnsetExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitReturn(O\ReturnExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    protected function visitThrow(O\ThrowExpression $expression)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }
} 