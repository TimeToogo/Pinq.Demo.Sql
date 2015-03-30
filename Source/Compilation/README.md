Compilation Structure
=====================

It is highly recommended to read [this][0] documentation of the query/expression API
to help grasp the structure of the compilation stage.

For the special case of mapping the PINQ query API to another dedicated query
DSL the PINQ library contains an extensive platform to implement this in a
powerful and efficient way. The source for this can be viewed [here][1]. 

The entry point for this is in the `IQueryCompilerConfiguration`/`IRepositoryCompilerConfiguration`
(as we are implementing the `IRepository` API the repository interface version is used).

There is no doubt that the process parsing then compiling PHP code is a prohibitively
expensive operation. PINQ's implementation of query compilation relies heavily on
the assumption that [a cache will be used][2] when using such features in production.
For this reason, it is recommended that the final results of compilation are lightweight
and optimized to run fresh from the cache. In regards to this, query compilation is 
separated into three main sections.

Structural Parameters
=====================

Due to the large mismatch between a dynamic programming language such as PHP
and other query DSLs (SQL) there are certain nuances that a completely valid
PHP code but near impossible to translate into equivalent SQL. For instance
take a straightforward query:

```php
$db->table('some_table')
    ->where(function ($row) use ($bar) { return $row['foo'] == $bar; })
```

This can be translated into SQL along the lines of:

```sql
SELECT * FROM some_table WHERE foo = ?
```

And this query can be used for many values of `$bar`
So this is all well and good but take another example:

```php
$db->table('some_table')
    ->where(function ($row) use ($bar) { return $bar($row['foo']) == 5 })
```

For a variable `$bar` determining the function call within the query, there
is no reasonable SQL equivalent. For these cases, PINQ provides an API for
handling such cases, coining the term *structural parameters*, prior to the compilation
stage, the structured query objects can be processed to locate and inline such
parameters before compilation. The applicable method for this are:

 - `SqlCompilerConfiguration::locateStructuralParameters`
 - `SqlCompilerConfiguration::inlineStructuralParameters`
 
If a structural parameter is located within a query, another level of caching
is applied. The structured query object will be cached as this contains the
query structure and parsed expression trees reducing the time required to
fully compile the query as this will can be reused and copied with the inlined 
the structural parameters as required. For instance, the structured query object
representing will be cached:

```php
$db->table('some_table')
    ->where(function ($row) use ($bar) { return $bar($row['foo']) == 5 })
```

This will be loaded each time when executing this query. If `$bar = 'strlen'` the
query object will be loaded, it will be processed to the equivalent query object of:

```php
$db->table('some_table')
    ->where(function ($row) { return strlen($row['foo']) == 5 })
```

This will then be compiled and the compilation of this cached as well, and the final
compiled version will be loaded from the cache for everytime this query is executed
with `$bar` equal to `'strlen'`.

More details on this can be found [here][3] 

Query Compilation
=================

This is where the magic happens :). The compilation stage involves interpreting
the structured query object to compile it into the desired DSL. The applicable methods
that are required to compile the query are:

 - [`SqlCompilerConfiguration::getRequestQueryCompiler`](SqlCompilerConfiguration.php),
 returns `Pinq\Providers\DSL\Compilation\Compilers\IRequestQueryCompiler`
 - [`SqlCompilerConfiguration::getOperationQueryCompiler`](SqlCompilerConfiguration.php),
 returns `Pinq\Providers\DSL\Compilation\Compilers\IOperationQueryCompiler`

In these methods, the compiler classes should be constructed with the necessary context
and returned. The key points about implementing this is taking advantage of PINQ's out of the
box classes designed for this purpose:

 - `Pinq\Expressions\ExpressionVisitor`
 - `Pinq\Providers\DSL\Compilation\Compilers\ScopeCompiler`
 - `Pinq\Providers\DSL\Compilation\Compilers\RequestQueryCompiler`
 - `Pinq\Providers\DSL\Compilation\Compilers\OperationQueryCompiler`
 
These classes follow the [visitor pattern][4] to provide simple ways to traverse
an entire structured query object. Another point is during the compilation process
the compiler classes have to maintain a lot of mutable state. This is represented
through intermediate query objects which act as the hold the compiled SQL and
extra data during compilation. These classes represent what is being compiled.

 - [`Select`](Select.php), implements `IRequestCompilation`
 - [`UpdateOrDelete`](UpdateOrDelete.php), implements `IOperationCompilation`

The SQL string is appended to an instance of one of these classes. For a more
advanced implementation of the `Select` class, it could contain individual fields for all
clauses of the SELECT statement which would remove the need to wrap the query
in a derived table when it not required.

Another important factor that is used throughout these classes is the
`Pinq\Providers\DSL\Compilation\Parameters\ParameterCollection` class which
provides a simple API to add the required parameters to the query. For instance,
with the example:

```php
$db->table('some_table')
    ->where(function ($row) { return $row['foo'] == $this->bar; })
```

When the expression tree for the `where` function is being compiled
it will come across a `FieldExpression` for `$this->bar`. This is not
a structural parameter and can be mapped to a standard prepared SQL query.
In this case it appropriate to append a parameter to the query and add
the expression to the `ParameterCollection` instance using the following method:

```php
// Signature
ParameterCollection::addExpression(
    O\Expression $expression,
    IParameterHasher $hasher,
    IFunction $context = null,
    $data = null
)

// In your expression compiler class
$this->query->parameters->addExpression($expression, ParameterHasher::valueType(), $this->functionContext, $extraData);
```

These parameters will be resolved to their concrete value when the query is executed.
In doing so, the `$this->bar` expression will be evaluated to retrieve the actual value.
This is where the function context comes into play. From the structured query object,
expression trees are retrieved through an instance of `Pinq\Queries\Functions\IFunction`
representing the function containing contextual data such as scope. What if `$this->bar`
was referencing a private/protected property and the function was within the class
scope? This information has to be kept with the parameter expressions to ensure that
correct scoping rules are applied when evaluating them.

More implementation details on the compilation stage can be found [here][5]

Compiled Queries
================

After the initial compilation has finished and the intermediate compilation object
is complete these are converted an instance of:

 - `Pinq\Providers\DSL\Compilation\ICompiledRequest`
 - `Pinq\Providers\DSL\Compilation\ICompiledOperation`

This is done via the `asCompiled` method defined on the `IQueryCompilation` interface.
As the compiled query objects are eligible to be cached, they should be immutable, lightweight and 
contain nothing but the bare minimum to execute the query and return the required data.
In this case that is the compiled SQL query string and the list of query parameters. The
`ParameterCollection` class implements a `buildRegistry` method which simply converts
the collection into an immutable equivalent which is then used when constructing the
compiled query object. The intermediate query objects contain builder methods to construct
the compiled query objects:

 - [`Select::asCompiled()`](Select.php)
 - [`UpdateOrDelete::asCompiled()`](UpdateOrDelete.php)

More details on the compiled queries can be found [here][6]

[0]: http://elliotswebsite.com/Pinq/queries-and-expressions.html
[1]: https://github.com/TimeToogo/Pinq/tree/master/Source/Providers/DSL
[2]: http://elliotswebsite.com/Pinq/performance.html
[3]: Preprocessors/
[4]: http://en.wikipedia.org/wiki/Visitor_pattern
[5]: Compilers/
[6]: Compiled/
