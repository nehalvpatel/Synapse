<?php

namespace Synapse;

/**
 * Class Handler
 */
class Handler implements \SessionHandlerInterface
{
    /**
     * @var \PDO PDO object
     */
    private $connection = null;

    /**
     * Constructs handler
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
        unset($connection);
    }

    /**
     * Opens session
     *
     * @param string $save_path
     * @param string $session_name
     *
     * @return boolean
     */
    public function open($save_path = "", $session_name = "")
    {
        // make sessions table if it doesn't exist
        $table_query = $this->connection->prepare("CREATE TABLE IF NOT EXISTS `sessions` (`ID` varchar(63) CHARACTER SET ascii NOT NULL DEFAULT '', `Data` text, `Expire` int(10) unsigned DEFAULT NULL, PRIMARY KEY (`ID`), KEY `Expire` (`Expire`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        return $table_query->execute();
    }

    /**
     * Close the session
     *
     * @return boolean
     */
    public function close()
    {
        $this->connection = null;

        return true;
    }

    /**
     * Read session data
     *
     * @param string $id Session id
     *
     * @return string Session data if available, empty string if not.
     */
    public function read($id)
    {
        $read_query = $this->connection->prepare("SELECT `Data` FROM `sessions` WHERE `ID` = :ID");
        $read_query->bindValue(":ID", $id);
        $read_query->execute();

        $read_results = $read_query->fetch();

        return $read_results["Data"];
    }

    /**
     * Write session data
     *
     * @param string $id Session id
     * @param string $data Session data to be written
     *
     * @return boolean
     */
    public function write($id, $data)
    {
        $write_query = $this->connection->prepare("INSERT INTO `sessions` (`ID`, `Data`, `Expire`) VALUES (:ID, :Data, :Expire) ON DUPLICATE KEY UPDATE `Data` = :Data, `Expire` = :Expire");
        $write_query->bindValue(":ID", $id);
        $write_query->bindValue(":Data", $data);
        $write_query->bindValue(":Expire", time() + ini_get("session.gc_maxlifetime"));

        return $write_query->execute();
    }

    /**
     * Destroy a session
     *
     * @param string $id Session id
     *
     * @return boolean
     */
    public function destroy($id)
    {
        $destroy_query = $this->connection->prepare("DELETE FROM `sessions` WHERE `ID` = :ID");
        $destroy_query->bindValue(":ID", $id);

        return $destroy_query->execute();
    }

    /**
     * Cleanup old sessions
     * Garbage collection
     *
     * @param string $maxlifetime Sessions that have not updated for the last maxlifetime seconds will be removed.
     *
     * @return boolean The return value (usually TRUE on success, FALSE on failure).
     */
    public function gc($maxlifetime)
    {
        $cleanup_query = $this->connection->prepare("DELETE FROM `sessions` WHERE `Expire` < :Old");
        $cleanup_query->bindValue(":Old", time() - $maxlifetime);

        return $cleanup_query->execute();
    }
}
