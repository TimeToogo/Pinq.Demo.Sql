<?php

namespace Pinq\Demo\Sql\Compilation\Compilers;

use Pinq\Demo\Sql;
use Pinq\Demo\Sql\Compilation\Select;
use Pinq\Demo\Sql\PinqDemoSqlException;
use Pinq\Demo\Sql\Compilation\Preprocessors\StaticOrdering;
use Pinq\Demo\Sql\Compilation\Preprocessors\StaticRange;
use Pinq\Expressions as O;
use Pinq\Providers\DSL\Compilation\Compilers\ScopeCompiler as ScopeCompilerBase;
use Pinq\Queries;
use Pinq\Queries\Requests;
use Pinq\Queries\Segments;
use Pinq\Queries\Segments\IndexBy;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ScopeCompiler extends ScopeCompilerBase
{
    /**
     * @var Select
     */
    protected $compilation;

    public function __construct(Queries\IScope $scope, Select $select)
    {
        parent::__construct($scope, $select);

        $this->compilation = $select;
        $this->compilation->sql = "SELECT * FROM {$this->compilation->tableName}";
    }

    /**
     * @return Select
     */
    protected function deriveSelect()
    {
        $this->compilation->wrapSelectAsDerivedTable();

        return $this->compilation;
    }

    /**
     * @param Queries\Functions\ProjectionBase $function
     *
     * @return string
     */
    protected function compileReturn(Queries\Functions\ProjectionBase $function)
    {
        return $this->compilation->expressionCompiler->compileReturn($function);
    }

    public function visitSelect(Segments\Select $query)
    {
        $elementProjection = $query->getProjectionFunction();

        // Ensure that the return values in the format:
        // [
        //    'alias' => <expr>,
        //    ...
        // ]
        $returnExpression  = $elementProjection->getReturnValueExpression();
        if (!($returnExpression instanceof O\ArrayExpression)) {
            throw new PinqDemoSqlException('Cannot select non array expression');
        }

        $originalSelect = $this->compilation->sql;

        $columns = [];
        foreach ($returnExpression->getItems() as $itemExpression) {
            if(!($itemExpression->getKey() instanceof O\ValueExpression)) {
                throw new PinqDemoSqlException('Select arrays cannot have variable keys');
            }

            $key = $itemExpression->getKey()->getValue();
            $columns[] = $this->compilation->expressionCompiler->compile($itemExpression->getValue(), $elementProjection) . ' AS ' . $key;
        }

        $this->compilation->sql = 'SELECT ';
        $this->compilation->sql .= implode(', ', $columns);
        $this->compilation->sql .= " FROM ({$originalSelect}) AS {$this->compilation->tableName}";
    }

    public function visitRange(Segments\Range $query)
    {
        if (!($query instanceof StaticRange)) {
            throw new PinqDemoSqlException('Range must be a static range class');
        }

        $this->compilation->sql .= ' LIMIT ';

        if($query->hasAmount()) {
            $this->compilation->sql .= $this->compilation->expressionCompiler->compile(O\Expression::value($query->getAmount()));
        } else {
            $this->compilation->sql .= '18446744073709551615';
        }

        $this->compilation->sql .= ' OFFSET ';
        $this->compilation->sql .= $this->compilation->expressionCompiler->compile(O\Expression::value($query->getStart()));
    }

    public function visitOrderBy(Segments\OrderBy $query)
    {
        $orderings = [];
        foreach ($query->getOrderings() as $ordering) {
            if (!($ordering instanceof StaticOrdering)) {
                throw new PinqDemoSqlException('Ordering must be a static ordering class');
            }

            $orderings[] = $this->compileReturn($ordering->getProjectionFunction()) . ' ' . ($ordering->isAscending() ? 'ASC' : 'DESC');
        }

        $this->deriveSelect();
        $this->compilation->sql .= ' ORDER BY ' . implode(', ', $orderings);
    }

    public function visitFilter(Segments\Filter $query)
    {
        $this->deriveSelect();
        $this->compilation->sql .= ' WHERE ' . $this->compileReturn($query->getProjectionFunction());
    }

    public function visitUnique(Segments\Unique $query)
    {
        $this->compilation->sql = "SELECT DISTINCT * FROM ({$this->compilation->sql}) AS {$this->compilation->tableName}";
    }

    public function visitKeys(Segments\Keys $query)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitReindex(Segments\Reindex $query)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitOperation(Segments\Operation $query)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitJoin(Segments\Join $query)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitGroupBy(Segments\GroupBy $query)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitSelectMany(Segments\SelectMany $query)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }

    public function visitIndexBy(IndexBy $segment)
    {
        throw PinqDemoSqlException::notSupported(__METHOD__);
    }
}
