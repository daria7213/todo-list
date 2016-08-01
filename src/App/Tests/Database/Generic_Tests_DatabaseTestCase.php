<?php
namespace App\Tests;

use Doctrine\DBAL\DriverManager;
use PDO;
use PHPUnit_Extensions_Database_TestCase;

abstract class Generic_Tests_DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;
    static private $dbal = null;

    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null)
        {
            if (self::$pdo == null)
            {
                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }

    /**
     * @return PDO
     */
    protected function getPdo()
    {
        return $this->getConnection()->getConnection();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDbal()
    {
        if (self::$dbal === null)
        {
            self::$dbal = DriverManager::getConnection(array('pdo' => $this->getPdo()));
        }

        return self::$dbal;
    }
}