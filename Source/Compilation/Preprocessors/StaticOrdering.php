<?php

namespace Pinq\Demo\Sql\Compilation\Preprocessors;

use Pinq\Queries\Functions;
use Pinq\Queries\Segments\Ordering;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StaticOrdering extends Ordering
{
    /**
     * @var boolean
     */
    protected $isAscending;

    public function __construct(Functions\ElementProjection $projectionFunction, $isAscending)
    {
        parent::__construct($projectionFunction, '');

        $this->isAscending = $isAscending;
    }

    public function getParameters()
    {
        return [];
    }

    /**
     * @return boolean
     */
    public function isAscending()
    {
        return $this->isAscending;
    }
} 