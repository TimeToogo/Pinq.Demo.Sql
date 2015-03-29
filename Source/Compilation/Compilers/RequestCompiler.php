<?php

namespace Pinq\Demo\Sql\Compilation\Compilers;

use Pinq\Demo\Sql;
use Pinq\Demo\Sql\Compilation\Select;
use Pinq\Demo\Sql\PinqDemoSqlException;
use Pinq\Providers\DSL\Compilation;
use Pinq\Providers\DSL\Compilation\Compilers\RequestQueryCompiler;
use Pinq\Queries;
use Pinq\Queries\Requests;
use Pinq\Queries\Segments;

class RequestCompiler extends RequestQueryCompiler
{
    /**
     * @var Select
     */
    protected $compilation;

    public function __construct(Queries\IRequestQuery $requestQuery, Select $select)
    {
        parent::__construct($requestQuery, $select, new ScopeCompiler($requestQuery->getScope(), $select));
    }

    public function visitValues(Requests\Values $request)
    {
        $this->compilation->retrievalMode = $request->getValuesType();
    }

    public function visitCount(Requests\Count $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitIsEmpty(Requests\IsEmpty $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitFirst(Requests\First $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitLast(Requests\Last $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitGetIndex(Requests\GetIndex $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitIssetIndex(Requests\IssetIndex $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitMaximum(Requests\Maximum $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitMinimum(Requests\Minimum $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitSum(Requests\Sum $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitAverage(Requests\Average $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitAll(Requests\All $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitAny(Requests\Any $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitImplode(Requests\Implode $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitContains(Requests\Contains $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitAggregate(Requests\Aggregate $request)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }
}
