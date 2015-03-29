<?php

namespace Pinq\Demo\Sql\Demo\Example;

use Pinq\Caching\ICacheAdapter;

/**
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CacheBench implements ICacheAdapter
{
    /**
     * @var ICacheAdapter
     */
    protected $cacheAdapter;

    public function __construct(ICacheAdapter $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    public function hasNamespace()
    {
        return $this->cacheAdapter->hasNamespace();
    }

    public function getNamespace()
    {
        return $this->cacheAdapter->getNamespace();
    }

    public function forNamespace($namespace)
    {
        return new self($this->cacheAdapter->forNamespace($namespace));
    }

    public function inGlobalNamespace()
    {
        return new self($this->cacheAdapter->inGlobalNamespace());
    }

    public function save($key, $value)
    {
        $this->cacheAdapter->save($key, $value);
    }

    public function contains($key)
    {
        return $this->cacheAdapter->contains($key);
    }

    public function tryGet($key)
    {
        $s = microtime(true);
        $value = $this->cacheAdapter->tryGet($key);
        var_dump('CACHE GET: ' . ((microtime(true) - $s) * 1000) . 'ms');
        return $value;
    }

    public function remove($key)
    {
        $this->cacheAdapter->remove($key);
    }

    public function clear()
    {
        $this->cacheAdapter->clear();
    }
}