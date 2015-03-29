Compiled Queries
================

The compiled queries are the final results of the compilation process.
As compilation is a very expensive process, the final results should
be cached. The compiled query objects implement one of:

 - `Pinq\Providers\DSL\Compilation\ICompiledRequest`
 - `Pinq\Providers\DSL\Compilation\ICompiledOperation`

They are lightweight, only containing the minimum amount of data to
execute the query and retrieve the results if need be.

 - [Compiled `SELECT` Class](Select.php)
 - [Compiled `UPDATE`/`DELETE` Class](UpdateOrDelete.php)