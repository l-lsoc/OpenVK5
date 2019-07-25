<?php
use Nette\Caching\Storages\FileStorage;
use Nette\Database;
use Nette\Database\Conventions\DiscoveredConventions;

class DB
{
    private $connection;
    private $context;
    
    private function __construct($dsn, $username, $password)
    {
        $storage          = new FileStorage(SOCN_ROOT."tmp/database");
        $this->connection = new Database\Connection($dsn, $user, $password);
        $structure        = new Database\Structure($this->connection, $storage);
        $conventions      = new DiscoveredConventions($structure);
        $this->context    = new Database\Context($this->connection, $structure, $conventions, $storage);
    }
    
    function getConnection(): Database\Connection
    {
        return $this->connection;
    }
    
    function getContext(): Database\Context
    {
        return $this->context;
    }
    
    static function getInstance()
    {
        if(!isset($GLOBALS["DB"])) $GLOBALS["DB"] = new DB(SOCN_CONFIG["DSN"], SOCN_CONFIG["DB_USER"], SOCN_CONFIG["DB_PASSWORD"]);
        
        return $GLOBALS["DB"];
    }
}
