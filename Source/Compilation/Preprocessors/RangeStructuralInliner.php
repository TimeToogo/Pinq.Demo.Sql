<?php

namespace Pinq\Demo\Sql\Compilation\Preprocessors;

use Pinq\Providers\DSL\Compilation\Parameters\ResolvedParameterRegistry;
use Pinq\Providers\DSL\Compilation\Processors\Visitors\ScopeProcessor;
use Pinq\Queries\Segments;
use Pinq\Queries;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RangeStructuralInliner extends ScopeProcessor
{
    /**
     * @var ResolvedParameterRegistry
     */
    protected $parameters;

    public function __construct(ResolvedParameterRegistry $parameters, Queries\IScope $scope)
    {
        parent::__construct($scope);
        $this->parameters = $parameters;
    }

    public function forSubScope(Queries\IScope $subScope)
    {
        return new self($this->parameters, $subScope);
    }

    public function visitRange(Segments\Range $segment)
    {
        return parent::visitRange(
                new StaticRange(
                        $this->parameters->getResolvedParameters()[$segment->getStartId()],
                        $this->parameters->getResolvedParameters()[$segment->getAmountId()]
                )
        );
    }
}