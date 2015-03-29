<?php

namespace Pinq\Demo\Sql\Compilation\Preprocessors;

use Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterHasher;
use Pinq\Providers\DSL\Compilation\Processors\Visitors\ScopeProcessor;
use Pinq\Queries\Segments;
use Pinq\Queries;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RangeStructuralLocator extends ScopeProcessor
{
    /**
     * @var ParameterCollection
     */
    protected $parameters;

    public function __construct(ParameterCollection $parameters, Queries\IScope $scope)
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
        $this->parameters->addId($segment->getStartId(), ParameterHasher::valueType());
        $this->parameters->addId($segment->getAmountId(), ParameterHasher::valueType());

        return parent::visitRange($segment);
    }
}