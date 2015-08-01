High Level API
==============

From an API point of view the PINQ library already provides the
bulk of the required API for this library in the 
[`ITraversable`][0] and the [`ICollection`][1] interfaces.
These interfaces defines the collection methods for querying so the
job of the library is to implement the mapping of this API to the
applicable backend, in this case MySQL.

As the collection API is already defined the entry point to this library
merely is a thin wrapper around the PINQ API:


```php
use Pinq\Demo\Sql\DB;

$db      = new DB($yourPdoConnection);
$results = $db->table('some_table')->where(function)...
```

The `DB::table` method returns an instance of [`IRepository`][2] which is
the counterpart interface of [`ICollection`][1] that delegates queries to an
underlying query provider.

More details on the PINQ query API can be found [here](http://elliotswebsite.com/Pinq/api.html).

[0]: https://github.com/TimeToogo/Pinq/blob/master/Source/ITraversable.php
[1]: https://github.com/TimeToogo/Pinq/blob/master/Source/ICollection.php
[2]: https://github.com/TimeToogo/Pinq/blob/master/Source/IRepository.php
