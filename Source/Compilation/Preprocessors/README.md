Query Preprocessors
===================

There are three main types of query preprocessors.

 - Expression processors - for all expression trees within a query
 - Structural expression processors - for all expression trees within a query
 - Query processors - for the scope/request/operations within the query
 
These classes can be overridden to transform a structured query object into the desired
form. As query objects are immutable, these processors will returns a new instance with
the changes. It is also possible to re-purpose the classes to just act as visitors without
changing the query structure.

Expression processors
=====================

The expression processor can be used if there is a requirement to transform expression trees
globally across an entire query. A good use case of this is simplifying complex expression
into an easier to compile form. For instance lets take a simple example of converting all
occurrences of the new `**` power binary operator and transform it into the old `pow` function.

```php
use Pinq\Providers\DSL\Compilation\Processors\Expression\ExpressionProcessor;
use Pinq\Providers\DSL\Compilation\Processors\Expression\ProcessorFactory;
use Pinq\Expressions as O;

class PowOperatorExpressionProcessor extends ExpressionProcessor
{
    public static function process(Queries\IQuery $query)
    {
        return ProcessorFactory::from($query, new self())->buildQuery();
    }
    
    public function walkBinaryOperation(O\BinaryOperationExpression $expression)
    {
        if ($expression->getOperator() === O\Operators\Binary::POWER) {
            return O\Expression::functionCall(
                O\Expression::value('pow'),
                [$this->walk($expression->getLeftOperand()), $this->walk($expression->getRightOperand())]
            );
        } 
        
        return parent::walkBinaryOperation($expression);
    }
}
```

The `ExpressionProcessor` class extends from `Pinq\Expressions\ExpressionWalker` and
has an additional `processFunction` method that will be called for every function in
the query. By default the will update the parameter and body expression trees of the function
using the expression walker functionality of that class. The `walkBinaryOperation` is
overridden, the method will be called for every instance of `BinaryOperationExpression`
in the expression tree. The return value is used as the replacement for that expression
in the new expression tree.

The static `process` method is a helper to actually process and create a transformed query.
It does so by constructing a `RequestQueryProcessor`/`OperationQueryProcessor` from the supplied
structured query object using the `ProcessorFactory::from` method which will process the supplied
query using the supplied `IExpressionProcessor` instance. So passing an instance of itself, the
`processFunction` method will be called to transform the query. Finally the `buildQuery` method
is called on the query processor which actually processes the query returning transformed structured
query object.

Before compiling the structured query object, this preprocessor can be used to transform all occurrences
of `**` operator into the `pow` function in a single line of code:

```php
$query = PowOperatorExpressionProcessor::process($query);
```

Structural expression processors
================================

The structural expression processor is a specialized version of the standard expression
processor above. The interface for a structural expression processor is rather simple:

 - [`Pinq\Providers\DSL\Compilation\Processors\Structure\IStructuralExpressionProcessor`][1]

A simple implementation of this is the [`DynamicFunctionCallProcessor`](DynamicFunctionCallProcessor.php).
This marks all variable function calls `$foo(...)` as structural parameters and will inline
them to the concrete value of `$foo`, so if `$foo = 'strlen'` then the expression will be
updated to `strlen(...)`.

The API of this interface is simple. The `matches` method is called for every expression in
the structured query object, the method should return a boolean value on whether this expression
counts as a structural parameter. If this method returns true, the `parameterize`/`inline` will
be called with the same expression.
 
 - The `parameterize` method should add the expression to the supplied instance of 
 `ParameterCollection` with the appropriate context. You should most likely use the
 `addParameter` method defined in the base `StructuralExpressionProcessor` class which
 will wrap the data in correct class and add it to the parameter collection.
 - The `inline` method will be called with the resolved parameter values and it should
 update the expression to the concrete expression tree that is able to be compiled.
 To find the correct parameter value, you can use the `getResolvedValue` method defined in
 the base class which will search the resolved parameters for the parameter with the correct
 expression instance.

To find all the structural parameters adding them to the  supplied parameter collection 
you can use the following:

```php
StructuralExpressionLocator::processQuery($parameterCollection, $query, new DynamicFunctionCallProcessor());
```

And to inline the structural parameters with their concrete values:

```php
$query = StructuralExpressionInliner::processQuery($resolvedParameters, $query, new DynamicFunctionCallProcessor());
```

You can see this code in action in the `SqlCompilerConfiguration` [here](../SqlCompilerConfiguration.php)

Query processors
================

It is also possible that structural parameters are not just within the expression trees
but part of the structured query object itself. In the case of SQL this is indeed true.
This project implements 2 separates cases where what is classified as a parameter in the
structured query object cannot in SQL. For example, the `OrderBy` query segment has the
order directions as a parameter to the query and SQL does not support variable order directions.

For these cases it is a little more complicated to implement the structural parametrization
and inlining but it follows the same process as with structural expression parameters.
The first step is to create a subclass of the class that represents the applicable part of
the query. For this case, we subclass the `Pinq\Queries\Segments\Ordering` class and require
the concrete value of the direction instead of a parameter id. This class is [`StaticOrdering`](StaticOrdering.php).
Next we require two classes that will locate and inline this parameter in the query. Since 
`OrderBy` is a segment within the query scope, these classes extend from:

```php
Pinq\Providers\DSL\Compilation\Processors\Visitors\ScopeProcessor
```

They override the `visitOrderBy` method which will be called for each `OrderBy` segment
in the query. They also implement the `forSubScope` method which is called for each query
external query scope that the query contains, this methods should returns an instance of
the processor classes that will process the supplied scope which allows the entire query
scope to be processed and updated. 

The [locator class](OrderDirectionStructuralLocator.php) takes the parameter collection and 
adds the necessary structural parameters via the `ParameterCollection::addId` method which 
takes the parameter id from the query object and an instance of (`IParameterHasher`) which
can be chosen from the implementations from the builder class `ParameterHasher`. This hashing
is important as it used to find the correct compiled query in the cache. For instance there
would be two cached compiled queries for each order direction (ASC/DESC). This parameter will
be hashed and part of the cache key for its compiled query. So when this query is executed,
the structural parameters are hashed and then the checked for a matching key in the cache
to find the matching compiled query so it does not have to recompiled every time. In this case,
the structural parameter is a boolean flag which means it can be hashed as a value type. Hence
`ParameterHasher::valueType()` is used. To use this class to located the structural parameters:

```php
(new OrderDirectionStructuralLocator($parameters, $query->getScope()))->buildScope()
```

The [inliner class](OrderDirectionStructuralInliner.php) takes the resolved structural parameters
and updates the scope with the concrete versions for each the `Pinq\Queries\Segments\Ordering`.
The order direction value is found via the `ResolvedParameterRegistry::getResolvedParameters()` method
which all the parameters associated by an id. The id from the `Ordering::getIsAscendingId()` method
is the same id that was used when locating it so it is used as the key of the resolved parameter array.
After this process the query scope should now only have `OrderBy` that only contain instances of
the `StaticOrdering` class and the order direction can be accessed through the `isAscending` method.
This can now be assumed in the compiler classes which gives the compiler classes the required data
to compile the query into SQL. This class can be used is much the same way except since the scope
is being transformed, we want to updated the original:

```php
$scope = (new OrderDirectionStructuralInliner($parameters, $scope))->buildScope();
```
 
[1]: https://github.com/TimeToogo/Pinq/blob/master/Source/Providers/DSL/Compilation/Processors/Structure/IStructuralExpressionProcessor.php