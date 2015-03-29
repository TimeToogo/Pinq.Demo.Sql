<?php

namespace Pinq\Demo\Sql\Tests\Integration\Compilation;

trait SqlAssertsIgnoringWhitespace
{
    protected function assertSqlEquals($expected, $actual, $message = '')
    {
        // Ignore whitespace and line breaks
        parent::assertSqlEquals(preg_replace('/\s/', '', $expected), preg_replace('/\s/', '', $actual), $message);
    }
}