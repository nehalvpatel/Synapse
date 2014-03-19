<?php

	class HandlerTest extends \PHPUnit_Framework_TestCase {
		/**
		* This is the object that will be tested
		* @var Synapse
		*/
		protected $object;
		
		/**
		* Mock pdo object
		* @var PDO
		*/
		static private $pdo = null;
		
		protected function setUp() {
			$this->pdo = new PDO("mysql:host=localhost;dbname=synapse_tests", "root", "");
			
			$this->synapse = new \Synapse\Handler($this->pdo);
			session_set_save_handler($this->synapse, true);
			if (session_status() === PHP_SESSION_NONE) { session_start(); }
		}
		
		protected function tearDown() {
			unset($this->synapse);
		}
		
		public function testOpen() {
			$this->synapse->open();
			
			$row_query = $this->pdo->prepare("SELECT * FROM `sessions` WHERE `ID` = :ID");
            $row_query->bindValue(":ID", session_id());
            $row_query->execute();
            
			$this->assertEquals(0, $row_query->rowCount());
		}
		
		/*
		* @depends testOpen
		*/
		public function testWrite() {
			$_SESSION["noodle"] = "6";
			
			$this->assertEquals(6, $_SESSION["noodle"]);
		}
		
		/*
		* @depends testWrite
		*/
		public function testRead() {
			$this->assertEquals(6, $_SESSION["noodle"]);
		}
		
		/*
		* @depends testRead
		*/
		public function testDestroy() {
			session_destroy();
            
			$row_query = $this->pdo->prepare("SELECT * FROM `sessions` WHERE `ID` = :ID");
            $row_query->bindValue(":ID", session_id());
            $row_query->execute();
            
			$this->assertEquals(0, $row_query->rowCount());
		}
		
		/*
		* @depends testDestroy
		*/
		public function testClose() {
			$close_response = $this->synapse->close();
			
			$this->assertTrue($close_response);
		}
        
        /*
		* @depends ?
		*/
        public function testGc() {
            $this->synapse->gc(ini_get("session.gc_maxlifetime") * -1);
            
            $row_query = $this->pdo->prepare("SELECT * FROM `sessions` WHERE `ID` = :ID");
            $row_query->bindValue(":ID", session_id());
            $row_query->execute();
            
            $this->assertEquals(0, $row_query->rowCount());
        }
	}

?>