<?php

namespace Pinq\Demo\Sql;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PinqDemoSqlException extends \Exception
{
    public static function notSupported($method)
    {
        return new self("Invalid call to {$method}: method is not supported");
    }
}
