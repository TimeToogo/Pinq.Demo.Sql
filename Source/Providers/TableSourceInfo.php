<?php

namespace Pinq\Demo\Sql\Providers;

use Pinq\Queries\SourceInfo;

/**
 * Implementation of the table source info.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableSourceInfo extends SourceInfo
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]|null
     */
    protected $primaryKeys;

    public function __construct($tableName, array $primaryKeys = null)
    {
        parent::__construct($tableName);

        $this->name        = $tableName;
        $this->primaryKeys = $primaryKeys;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasPrimaryKeys()
    {
        return $this->primaryKeys !== null;
    }

    /**
     * @return string[]|null
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }
} 