<?php

namespace Pinq\Demo\Sql\Compilation\Preprocessors;

use Pinq\Queries\Functions;
use Pinq\Queries\Segments\Range;

/**
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StaticRange extends Range
{
    /**
     * @var int
     */
    protected $start;

    /**
     * @var int|null
     */
    protected $amount;

    public function __construct($start, $amount)
    {
        parent::__construct('', '');

        $this->start = $start;
        $this->amount = $amount;
    }

    public function getParameters()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return bool
     */
    public function hasAmount()
    {
        return $this->amount !== null;
    }

    /**
     * @return int|null
     */
    public function getAmount()
    {
        return $this->amount;
    }
} 