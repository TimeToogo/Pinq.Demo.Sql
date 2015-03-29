Query Providers
===============

The query provider (or repository provider) are the classes responsible
mapping and loading the queries to and from the desired backend. These
classes also hold the required context for creating `IQueryable`/`IRepository`.

The context of of a `IQueryable`/`IRepository` in represented through an
implementation of `Pinq\Queries\ISourceInfo`. For this project a `IRepository`
represents a database table, so the implementation, [`TableSourceInfo`](TableSourceInfo.php),
contains the table name and some other fields required for some queries.

In this case, mapping the queries to another dedicated query DSL (MySQL), the
package extends specialized query providers under the `Pinq\Providers\DSL` namespace
which delegates the query compilation to a separate class implementing
`IQueryCompilerConfiguration`/`IRepositoryCompilerConfiguration` (as we are implementing
the `IRepository` API the repository interface version is used). Details
of the compilation section can be found [here][1].

Queries which return data (part of the `IQueryable` interface) are loaded through
the query provider and queries which mutate data (part of the `IRepository` interface)
are executed through the repository provider. The applicable methods are:

 - `Pinq\Providers\DSL\QueryProvider::loadCompiledRequest` - [Implementation](TableQueryProvider.php)
 - `Pinq\Providers\DSL\RepositoryProvider::executeCompiledOperation` - [Implementation](TableRepositoryProvider.php)

They are passed the compiled query objects and resolved parameters required to
execute the query. In this implementation these methods delegate to methods on
the compiled query objects to actually execute the query. This is a judgement
call, the implementation for this is just as well suited to be in the query provider
class itself.

Query Optimization
==================

Out of the box, the default `QueryProvider` class provides a lot default functionality.
One of these features is the usage of a helper class [`Pinq\Providers\Utilities\IQueryResultCollection`][2],
This performs a special optimization that can take place when implementing the PINQ API.
As the query language is now standard PHP code and PINQ already offers a full in-memory
implementation of the API running through PHP, the query provider can delegate any queries
where the data has already been loaded into memory to the PHP implementation. Take the following
example:

```php
$query = $db->table('some_table')
            ->where(function ($row) { return $row['foo'] < 50; });
            
$results = $query->asArray();
$subScopeQuery = $query->where(function ($row) { return $row['foo'] > 20; });
$filteredResults = $subScopeQuery->asArray();
```

In the above code, we can see how the first query must be compiled and the result set returned
as an array. Interestingly for the second query there is another option, use the loaded result set
from the first query and then filter that in memory using the PHP implementation of the query API. 
This will avoid unnecessary queries to the database completely in a completely unobtrusive way.

This optimization is opt-in and can be overridden by changing the default instance of
`Pinq\Providers\Configuration\DefaultRepositoryConfiguration`/`Pinq\Providers\Configuration\DefaultQueryConfiguration`
and overriding `shouldUseQueryResultCaching` and return whether this should be used.
If more control is required, you can override `getQueryResultCollection` method to provide
your own implementation of `IQueryResultCollection`.

[1]: ../Compilation/README.md
[2]: https://github.com/TimeToogo/Pinq/blob/master/Source/Providers/Utilities/IQueryResultCollection.php
