<?php

namespace Pinq\Demo\Sql\Compilation;

use PDO;
use Pinq\Demo\Sql\Compilation\Compilers\OperationCompiler;
use Pinq\Demo\Sql\Compilation\Compilers\RequestCompiler;
use Pinq\Demo\Sql\Compilation\Preprocessors\DynamicFunctionCallProcessor;
use Pinq\Demo\Sql\Providers\RepositoryConfiguration;
use Pinq\Demo\Sql\Providers\TableSourceInfo;
use Pinq\Demo\Sql\Compilation\Preprocessors\OrderDirectionStructuralInliner;
use Pinq\Demo\Sql\Compilation\Preprocessors\OrderDirectionStructuralLocator;
use Pinq\Demo\Sql\Compilation\Preprocessors\RangeStructuralInliner;
use Pinq\Demo\Sql\Compilation\Preprocessors\RangeStructuralLocator;
use Pinq\Providers\Configuration;
use Pinq\Providers\DSL\Compilation;
use Pinq\Providers\DSL\Compilation\Compilers\IOperationQueryCompiler;
use Pinq\Providers\DSL\Compilation\Compilers\IRequestQueryCompiler;
use Pinq\Providers\DSL\Compilation\Parameters;
use Pinq\Providers\DSL\Compilation\Processors\Structure\StructuralExpressionInliner;
use Pinq\Providers\DSL\Compilation\Processors\Structure\StructuralExpressionLocator;
use Pinq\Providers\DSL\RepositoryCompilerConfiguration;
use Pinq\Queries;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SqlCompilerConfiguration extends RepositoryCompilerConfiguration
{
    /**
     * @var PDO
     */
    protected $connection;

    public function __construct(PDO $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function buildQueryConfiguration()
    {
        return new RepositoryConfiguration();
    }

    protected function locateStructuralParameters(Queries\IQuery $query)
    {
        $parameters = new Parameters\ParameterCollection();
        
        (new OrderDirectionStructuralLocator($parameters, $query->getScope()))->buildScope();
        (new RangeStructuralLocator($parameters, $query->getScope()))->buildScope();

        StructuralExpressionLocator::processQuery($parameters, $query, new DynamicFunctionCallProcessor());

        return $parameters->buildRegistry();
    }

    protected function inlineStructuralParameters(
            Queries\IQuery $query,
            Parameters\ResolvedParameterRegistry $parameters
    ) {
        $scope = $query->getScope();

        $scope = (new OrderDirectionStructuralInliner($parameters, $scope))->buildScope();
        $scope = (new RangeStructuralInliner($parameters, $scope))->buildScope();
        $query = $query->updateScope($scope);

        $query = StructuralExpressionInliner::processQuery($parameters, $query, new DynamicFunctionCallProcessor());

        return $query;
    }

    /**
     * @param Queries\IRequestQuery $query
     *
     * @return IRequestQueryCompiler
     */
    protected function getRequestQueryCompiler(Queries\IRequestQuery $query)
    {
        /** @var TableSourceInfo $source */
        $source = $query->getScope()->getSourceInfo();

        $select = new Select(
            $this->connection,
            $source,
            new Parameters\ParameterCollection()
        );

        return new RequestCompiler($query, $select);
    }

    /**
     * @param Queries\IOperationQuery $query
     *
     * @return IOperationQueryCompiler
     */
    protected function getOperationQueryCompiler(Queries\IOperationQuery $query)
    {
        /** @var TableSourceInfo $source */
        $source = $query->getScope()->getSourceInfo();

        $updateOrDelete = new UpdateOrDelete(
            $this->connection,
            $source,
            new Parameters\ParameterCollection()
        );

        return new OperationCompiler($query, $updateOrDelete);
    }
}