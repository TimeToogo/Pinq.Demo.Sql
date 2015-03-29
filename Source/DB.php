<?php

namespace Pinq\Demo\Sql;

use PDO;
use Pinq\Demo\Sql\Compilation\SqlCompilerConfiguration;
use Pinq\Demo\Sql\Providers\TableSourceInfo;
use Pinq\IRepository;

/**
 * Entry point to the Pinq.Demo.Sql API.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DB
{
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var Providers\TableRepositoryProvider[]
     */
    protected $tableProviders;

    /**
     * @var SqlCompilerConfiguration
     */
    protected $compilerConfiguration;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->compilerConfiguration = new SqlCompilerConfiguration($this->connection);
    }

    /**
     * Gets the underlying PDO instance.
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Gets a IRepository instance for the supplied table.
     * Primary keys must be supplied for calls to ->clear() and ->apply()
     *
     * @param string        $name
     * @param string[]|null $primaryKeys
     *
     * @return IRepository
     */
    public function table($name, array $primaryKeys = null)
    {
        if (!isset($this->tableProviders[$name])) {
            $this->tableProviders[$name] = new Providers\TableRepositoryProvider(
                $this->connection,
                new TableSourceInfo($name, $primaryKeys),
                $this->compilerConfiguration
            );
        }

        return $this->tableProviders[$name]->createRepository();
    }
} 