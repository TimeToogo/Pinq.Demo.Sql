<?php

namespace Pinq\Demo\Sql\Demo\Example;

use Pinq\Demo\Sql\DB;
use Pinq\Caching\CacheProvider;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Example
{
    /**
     * @var DB
     */
    protected $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    const RETRIEVE        = 0;
    const RETRIEVE_COMPLEX = 1;
    const RETRIEVE_COMPLEX_2 = 2;

    public static function run(DB $db, $action)
    {
        $example = new self($db);
        switch ($action) {
            case self::RETRIEVE:
                $example->retrieve();
                break;

            case self::RETRIEVE_COMPLEX:
                $example->retrieveComplex();
                break;

            case self::RETRIEVE_COMPLEX_2:
                $example->retrieveComplex2();
                break;
        }
    }


    private function retrieve()
    {
        ($this->db->table('blogs')->asArray());
    }

    private function retrieveComplex()
    {
        $v = 1000;
        ($this->db->table('blogs')
                ->where(function ($i) { return $i['Id'] > 0; })
                ->where(function ($i) { return $i['Id'] > 0; })
                ->where(function ($i) { return $i['Id'] > 0; })
                ->where(function ($i) { return $i['Id'] . 'foo' != 'foobar'; })
                ->where(function ($i) use($v){ return $i['Id'] > $v; })
                ->asArray());
    }

    private function retrieveComplex2()
    {
        $v = 1000;
        ($this->db->table('blogs')
                        ->where(function ($i) { return $i['Id'] > 0; })
                        ->where(function ($i) { return $i['Id'] > 0; })
                        ->where(function ($i) { return $i['Id'] > 0; })
                        ->where(function ($i) { return $i['Id'] . 'foo' != 'foobar'; })
                        ->where(function ($i) use($v){ return $i['Id'] > $v; })
                        ->asArray());
    }
}

require_once '../../vendor/autoload.php';
require_once 'CacheBench.php';
echo '<pre>';

$pdo = new \PDO('mysql:host=localhost;dbname=penumbratest', 'root', 'admin');

$cacheType = 1;
$devMode   = false;

switch($cacheType)
{
    case 1:
        CacheProvider::setFileCache('cache.cache');
        break;
    case 2:
        CacheProvider::setDirectoryCache(__DIR__ . '/cache');
        break;
    case 3:
        $memcache = new \Memcache();
        $memcache->connect('localhost', 11211);
        $memcacheCache = new MemcacheCache();
        $memcacheCache->setMemcache($memcache);
        CacheProvider::setDoctrineCache($memcacheCache);
        break;
}

set_error_handler(function () {
    echo '<pre>';
    debug_print_backtrace();
    echo '</pre>';
});

CacheProvider::setCustomCache(new CacheBench(CacheProvider::getCacheAdapter()));
CacheProvider::setDevelopmentMode($devMode);

$db = new DB($pdo);

//include files
$start = microtime(true);
Example::run($db, Example::RETRIEVE);
$end = microtime(true);
var_dump('PINQ SIMPLE TIME TAKEN: ' . (1000 * ($end - $start)) . 'ms');

//PINQ 1
$start = microtime(true);
Example::run($db, Example::RETRIEVE_COMPLEX);
$end = microtime(true);
var_dump('PINQ COMPLEX 1 TIME TAKEN: ' . (1000 * ($end - $start)) . 'ms');

//PINQ 1
$start = microtime(true);
Example::run($db, Example::RETRIEVE_COMPLEX);
$end = microtime(true);
var_dump('PINQ COMPLEX 1 TIME TAKEN: ' . (1000 * ($end - $start)) . 'ms');

//PINQ 2
$start = microtime(true);
Example::run($db, Example::RETRIEVE_COMPLEX_2);
$end = microtime(true);
var_dump('PINQ COMPLEX 2 TIME TAKEN: ' . (1000 * ($end - $start)) . 'ms');

//PINQ 2
$start = microtime(true);
Example::run($db, Example::RETRIEVE_COMPLEX_2);
$end = microtime(true);
var_dump('PINQ COMPLEX 2 TIME TAKEN: ' . (1000 * ($end - $start)) . 'ms');

//NATIVE
$start = microtime(true);
$query = $pdo->prepare('SELECT * FROM (SELECT * FROM (SELECT * FROM (SELECT * FROM (SELECT * FROM (SELECT * FROM `blogs`) AS `s0` WHERE (`Id` > :p0)) AS `s1` WHERE (`Id` > :p1)) AS `s2` WHERE (`Id` > :p2)) AS `s3` WHERE (`Id` > :p3)) AS `s4` WHERE (`Id` > :p4) LIMIT 1');
$query->execute([':p0' => 0, ':p1' => 0, ':p2' => 0, ':p3' => 0, ':p4' => 1000]);
$query->fetchAll();
$end = microtime(true);
var_dump('NATIVE TIME TAKEN: ' . (1000 * ($end - $start)) . 'ms');

