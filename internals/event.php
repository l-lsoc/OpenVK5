<?php
class EventManager
{
    private $since;
    private $connection;

    private function __construct()
    {
        $this->since = time();
        $this->connection = new PDO( 
            'sqlite:../internals/events.bin', 
            null, 
            null, 
            [PDO::ATTR_PERSISTENT => true]
        );
        $this->connection->query("CREATE TABLE IF NOT EXISTS pool(id INTEGER PRIMARY KEY AUTOINCREMENT, since INTEGER, for INTEGER, event TEXT);");
    }
    
    private function eventFor(int $for): ?array
    {
        $since     = $this->since - 1;
        $statement = $this->connection->query("SELECT * FROM pool WHERE `for` = $for AND `since` > $since ORDER BY since DESC");
        $event     = $statement->fetch(PDO::FETCH_LAZY);
        if(!$event) return null;
        
        $this->since = time();
        return [$event->id, unserialize(hex2bin($event->event))];
    }
    
    function listen(Closure $callback, int $for): void
    {
        $this->since = time() - 1;
        for($i = 0; $i < 25; $i++) {
            list($id, $evt) = $this->eventFor($for);
            $id = crc32($id);
            if(!is_null($evt)) $callback($evt, $id);
            sleep(1);
        }
        
        exit("[]");
    }
    
    function triggerEvent(object $event, int $for): bool
    {
        $event = bin2hex(serialize($event));
        $since = time();
        
        $this->connection->query("INSERT INTO pool VALUES (NULL, $since, $for, '$event')");
        return true;
    }
    
    static function getInstance()
    {
        if(!isset($GLOBALS["EM"])) $GLOBALS["EM"] = new EventManager();
        
        return $GLOBALS["EM"];
    }
}
