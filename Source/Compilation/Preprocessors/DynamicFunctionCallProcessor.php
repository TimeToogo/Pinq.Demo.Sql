<?php

namespace Pinq\Demo\Sql\Compilation\Preprocessors;

use Pinq\Expressions as O;
use Pinq\Providers\DSL\Compilation\Parameters;
use Pinq\Providers\DSL\Compilation\Processors\Structure\StructuralExpressionProcessor;
use Pinq\Queries;
use Pinq\Queries\Functions\IFunction;
use Pinq\Queries\Segments;

/**
 * Converts dynamic function calls in the form $variable(...)
 * into structural parameters and inlines the values into
 * concrete function call expressions.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DynamicFunctionCallProcessor extends StructuralExpressionProcessor
{
    /**
     * Whether the supplied expression should be processed as structural expression.
     *
     * @param IFunction $function
     * @param O\Expression $expression
     *
     * @return boolean
     */
    public function matches(
        IFunction $function,
        O\Expression $expression
    ) {
        return $expression instanceof O\InvocationExpression
            && $expression->getValue() instanceof O\VariableExpression;
    }

    /**
     * Adds the necessary expression(s) to the supplied collection.
     *
     * @param IFunction $function
     * @param O\Expression $expression
     * @param Parameters\ParameterCollection $parameters
     *
     * @return void
     */
    public function parameterize(
        IFunction $function,
        O\Expression $expression,
        Parameters\ParameterCollection $parameters
    ) {
        /** @var O\InvocationExpression $expression */
        $this->addParameter($parameters, $function, $expression->getValue());
    }

    /**
     * Updates the matched expression with it's resolved value from
     * the supplied registry.
     *
     * @param IFunction $function
     * @param O\Expression $expression
     * @param Parameters\ResolvedParameterRegistry $parameters
     *
     * @return O\Expression
     */
    public function inline(
        IFunction $function,
        O\Expression $expression,
        Parameters\ResolvedParameterRegistry $parameters
    ) {
        /** @var O\InvocationExpression $expression */
        return O\Expression::functionCall(
            O\Expression::value($this->getResolvedValue($parameters, $expression->getValue())),
            $expression->getArguments()
        );
    }
}