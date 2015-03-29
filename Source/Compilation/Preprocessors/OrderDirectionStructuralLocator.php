<?php

namespace Pinq\Demo\Sql\Compilation\Preprocessors;

use Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection;
use Pinq\Providers\DSL\Compilation\Parameters\ParameterHasher;
use Pinq\Providers\DSL\Compilation\Processors\Visitors\ScopeProcessor;
use Pinq\Queries;
use Pinq\Queries\Segments;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderDirectionStructuralLocator extends ScopeProcessor
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

    public function visitOrderBy(Segments\OrderBy $segment)
    {
        foreach($segment->getOrderings() as $ordering) {
            $this->parameters->addId($ordering->getIsAscendingId(), ParameterHasher::valueType());
        }

        return parent::visitOrderBy($segment);
    }
}