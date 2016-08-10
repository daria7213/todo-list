<?php
namespace App\Tests;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use Silex\Application;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

class UserRepositoryTest extends Generic_Tests_DatabaseTestCase {

    static private $repository;
    static private $validator;
    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/todo_db.xml');
    }

    protected function getRepository(){

        if (self::$repository === null)
        {
            self::$repository = new UserRepository($this->getDbal(), new BCryptPasswordEncoder(4),self::$validator);
        }

        return self::$repository;
    }

    public function setUp()
    {
        $this->getPdo()->query("set foreign_key_checks=0");
        parent::setUp();
        if(self::$validator == null) {
            self::$validator = $this->createMock(\Symfony\Component\Validator\Validator\ValidatorInterface::class);
        }
        self::$validator
        ->method('validate')
        ->will($this->returnCallback(
            function($param){
                if($param->getRealUsername() == 'valid'){
                    return [];
                }
                return ['dbal'=>['error']];
            }
        ));
        $this->getPdo()->query("set foreign_key_checks=1");
    }

    public function testFind(){
        $user = $this->getRepository()->find(1);
        $this->assertEquals($user->getRealUsername(), 'user1');
    }

    public function testFindAllEmpty(){

    }

    public function testValidSave(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Pre adding user");
        $user = new User(null,'valid','user3@user3','password','ROLE_USER', new \DateTime('2016-07-11'));
        $this->assertEmpty($this->getRepository()->save($user));
        $queryTable = $this->getConnection()->createQueryTable(
            'users', 'SELECT * FROM users'
        );
        $expectedTable = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/expectedAdd.xml')
            ->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $this->assertEquals(3, $this->getConnection()->getRowCount('users'), "Post adding user");
    }

    public function testInvalidSave(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Pre adding user");
        $user = new User(null,'invalid','user3@user3','password','ROLE_USER', new \DateTime('2016-07-11'));
        $this->assertNotEmpty($this->getRepository()->save($user));
        $queryTable = $this->getConnection()->createQueryTable(
            'users', 'SELECT * FROM users'
        );
        $expectedTable = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/todo_db.xml')
            ->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Post adding user");
    }

    public function testDelete(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "pre deleting user");
        $this->getRepository()->delete(1);
        $this->assertTableRowCount('users',1);
    }

    public function testUniqueEmail(){
        $user = $this->getRepository()->find(1);
        $user2 = $this->getRepository()->find(2);
        $user->setEmail($user2->getEmail());

        $this->assertNotEmpty($this->getRepository()->update($user));
    }

    public function testValidUpdate(){
        $user = $this->getRepository()->find(1);
        $user->setUsername('valid');

        $this->assertEmpty($this->getRepository()->update($user));
        $queryTable = $this->getConnection()->createQueryTable(
            'users', 'SELECT * FROM users'
        );
        $expectedTable = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/expectedEdited.xml')
            ->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testInvalidUpdate(){
        $user = $this->getRepository()->find(1);
        $user->setUsername('invalid');

        $this->assertNotEmpty($this->getRepository()->update($user));
        $queryTable = $this->getConnection()->createQueryTable(
            'users', 'SELECT * FROM users'
        );
        $expectedTable = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/todo_db.xml')
            ->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testFindByEmailFalse(){
        $user = $this->getRepository()->findByEmail('test');
        $this->assertFalse($user);
    }

    public function testFindByEmailFalseTrue(){
        $user = $this->getRepository()->findByEmail('user1@user1');
        $this->assertEquals($user->getId(),1);
    }
}