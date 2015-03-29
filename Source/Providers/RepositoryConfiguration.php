<?php

namespace Pinq\Demo\Sql\Providers;

use Pinq\Providers\Configuration\DefaultRepositoryConfiguration;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RepositoryConfiguration extends DefaultRepositoryConfiguration
{
    protected function shouldUseQueryResultCaching()
    {
        return true;
    }
}