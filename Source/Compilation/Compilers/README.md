Compilers
=========

There are four main types of compiler classes.

 - Expression compiler - for compiling the query expression trees.
 - Scope compiler - for compiling the query scope.
 - Request compiler - for compiling the request part of the query and linking it with the scope.
 - Operation compiler - for compiling the operation part of the query and linking it with the scope.

These classes all follow the [visitor pattern][1].

Expression Compiler
===================

This class would most likely extend `Pinq\Expressions\ExpressionVisitor` providing
the functionality to traverse the expression tree. In this case we are compiling the
expression trees into a SQL string. This class defines a method
`compile(O\Expression $expression, FunctionBase $functionContext = null)` which will
compile the supplied expression tree into SQL and return it as a string and append any
expression parameters under the function context if supplied. There is a significant amount
of PHP operators and expressions the map very cleanly to a SQL equivalent such as math
operators, these are implemented as an associative array which is used to find matching operators. 
But there are also exceptions, the PHP concatenation operator `.` has no equivalent operator, so
this is regarded as a special case, the `visitBinaryOperation` will check to see of the operator
is `.` and handle this case explicitly, mapping it to the `CONCAT` SQL function. This pattern
can be applied to most of the cases but only as this remains a rather limited and simple implementation.
There are many expressions that are unsupported and will just throw an exception if used.
In a more advanced implementation, perhaps mapping OO APIs to SQL, it would be beneficial to
restructure the expression compiler to be more modularized with many classes handling
different types of expressions and API.

[Current Implementation](ExpressionCompiler.php)

Scope Compiler
==============

The scope compiler is a level above the expression compiler. Where instead of compiling
SQL expressions, it compiles to clauses of a SELECT query. One important note about mapping
the scope to a SQL equivalent is the differing order of operations. As the PINQ api is
just a fluent interface, the scope segments can be freely ordered by the user, this is not
the case with a SELECT statement where the order of operations are clearly defined. To
overcome this issue, the scope compiler simply wraps every compiled segment in as a derived
table. In a more advanced implementation, the scope compiler could determine where this
is necessary for each segment or if it happens to match the SQL order of operations and
can be compiled to a cleaner query. This implementation only supports a small subset of
the PINQ API which has a clean SQL equivalent.

[Current Implementation](ScopeCompiler.php)

Request compiler
================

The request compiler for this implementation only supports retrieve the values
from the generated SELECT from the query scope. It simply sets a flag on the
compilation object determining how the results set should be returned. You
can view the how this used in the compiled select object [here](../Compiled/Select.php).
Many of these methods would not be difficult to implement, for instance the `visitCount`
method could simple wrap the SELECT in a `SELECT COUNT(*) FROM (...)` and set a flag
to return the first value of the results.

[Current Implementation](RequestCompiler.php)

Operation Compiler
==================

The operation compiler supports mapping the `->apply(...)` operation to a
SQL `UPDATE` query and the `->clear()` operation to an equivalent `DELETE` query.
As this is a basic implementation where the scope compiler compiles directly to
a SELECT query, this generates some non-idiomatic SQL to keep the implementation simple.
The SELECT generated from the scope compiler is simply joined to the original table
via the primary key so the query only applies to the rows defined in the scope. This
obliviously requires the primary keys of the table and is the reason why the user must
supply them if to perform these queries. In a more advanced implementation, full schema
introspection could be allow these features unobtrusively. 

[Current Implementation](OperationCompiler.php)

[1]: http://en.wikipedia.org/wiki/Visitor_pattern
