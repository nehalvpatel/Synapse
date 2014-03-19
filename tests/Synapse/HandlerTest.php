<?php

/**
 * HandlerTest Class Doc Comment
 *
 * @category Tests
 * @package  Synapse
 * @author   Nehal Patel <nehal@itspatel.com>
 * @license  http://opensource.org/licenses/MIT MIT license
 * @link     https://packagist.org/packages/nehalvpatel/synapse
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This is the object that will be tested
     * @var Synapse
     */
    protected $object;
    
    /**
     * Mock pdo object
     * @var PDO
     */
    static private $_pdo = null;
    
    /** Sets up test environment
    *
    * @return void
    */
    protected function setUp()
    {
        $this->_pdo = new PDO("mysql:host=localhost;dbname=synapse_tests", "root", "");
        
        $this->synapse = new \Synapse\Handler($this->_pdo);
        session_set_save_handler($this->synapse, true);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /** Destroys test environment
    *
    * @return void
    */
    protected function tearDown()
    {
        unset($this->synapse);
    }
    
    /** Tests session open call
    *
    * @return void
    */
    public function testOpen()
    {
        $this->synapse->open();
        
        $row_query = $this->_pdo->prepare("SELECT * FROM `sessions` WHERE `ID` = :ID");
        $row_query->bindValue(":ID", session_id());
        $row_query->execute();
        
        $this->assertEquals(0, $row_query->rowCount());
    }
    
    /**
     * Tests data write call
     * 
     * @depends testOpen
     *
     * @return void
     */
    public function testWrite()
    {
        $_SESSION["noodle"] = "6";
        
        $this->assertEquals(6, $_SESSION["noodle"]);
    }
    
    /**
     * Tests data read call
     *
     * @depends testWrite
     *
     * @return void
     */
    public function testRead()
    {
        $this->assertEquals(6, $_SESSION["noodle"]);
    }
    
    /**
     * Tests session destroy call
     *
     * @depends testRead
     *
     * @return void
     */
    public function testDestroy()
    {
        session_destroy();
        
        $row_query = $this->_pdo->prepare("SELECT * FROM `sessions` WHERE `ID` = :ID");
        $row_query->bindValue(":ID", session_id());
        $row_query->execute();
        
        $this->assertEquals(0, $row_query->rowCount());
    }
    
    /**
     * Tests session close call
     *
     * @depends testDestroy
     *
     * @return void
     */
    public function testClose()
    {
        $close_response = $this->synapse->close();
        
        $this->assertTrue($close_response);
    }
    
    /**
     * Tests garbage collection
     *
     * @depends testClose
     *
     * @return void
     */
    public function testGc()
    {
        $this->synapse->gc(ini_get("session.gc_maxlifetime") * -1);
        
        $row_query = $this->_pdo->prepare("SELECT * FROM `sessions` WHERE `ID` = :ID");
        $row_query->bindValue(":ID", session_id());
        $row_query->execute();
        
        $this->assertEquals(0, $row_query->rowCount());
    }
}
