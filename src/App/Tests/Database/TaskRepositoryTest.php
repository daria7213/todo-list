<?php
namespace App\Tests;
use App\Entity\Task;
use App\Repository\TaskRepository;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use Symfony\Component\Config\Definition\Exception\Exception;

class TaskRepositoryTest extends Generic_Tests_DatabaseTestCase {

    static private $repository;
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
            self::$repository = new TaskRepository($this->getDbal());
        }

        return self::$repository;
    }

    public function setUp()
    {
        $this->getPdo()->query("set foreign_key_checks=0");
        parent::setUp();
        $this->getPdo()->query("set foreign_key_checks=1");
    }

    public function testCreateRepository(){
        $rep = new TaskRepository($this->getDbal());
        $this->assertNotNull($rep);
    }

    public function testSave(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('tasks'), "pre adding task");
        $task = new Task(null,'1','task3','false',new \DateTime('2016-05-30'),'true',new \DateTime('2017-01-17'));
        $this->getRepository()->save($task);
        $queryTable = $this->getConnection()->createQueryTable(
            'tasks', 'SELECT * FROM tasks'
        );
        $expectedTable = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/expectedAdd.xml')
            ->getTable("tasks");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $this->assertEquals(3, $this->getConnection()->getRowCount('tasks'), "post adding task");
    }

    public function testFind(){
        $task = $this->getRepository()->find(1);
        $this->assertEquals($task->getText(), 'task1');
    }

    public function testFindAll(){
        $countBefore = count($this->getRepository()->findAll());
        $this->getRepository()->save($task = new Task(null,'1','task3','false',new \DateTime('2016-05-30'),'true',new \DateTime('2017-01-17')));
        $countAfter = count($this->getRepository()->findAll());

        $this->assertEquals($countAfter,$countBefore+1);
    }

    public function testFindAllEmpty(){
        $this->expectException(Exception::class);
        $this->getDbal()->executeQuery('DELETE FROM tasks');

        $this->getRepository()->findAll();
    }

    public function testFindAllByUserId(){
        $countBefore = count($this->getRepository()->findAllByUserId(1));
        $this->getRepository()->save($task = new Task(null,'1','task3','false',new \DateTime('2016-05-30'),'true',new \DateTime('2017-01-17')));
        $countAfter = count($this->getRepository()->findAllByUserId(1));

        $this->assertEquals($countAfter,$countBefore+1);
    }

    public function testFindAllByUserIdEmpty(){
        $this->expectException(Exception::class);
        $this->getDbal()->executeQuery('DELETE FROM tasks WHERE user_id = 1');

        $this->getRepository()->findAllByUserId(1);
    }

    public function testDelete(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('tasks'), "pre deleting task");
        $this->getRepository()->delete('1');
        $this->assertTableRowCount('tasks',1);
    }

    public function testUpdate(){
        $task = $this->getRepository()->find(1);
        $task->setText('edited task');
        $this->getRepository()->update($task);

        $task2 = $this->getRepository()->find(2);
        $task2->setDate(new \DateTime('2019-07-21'));
        $this->getRepository()->update($task2);

        $queryTable = $this->getConnection()->createQueryTable(
            'tasks', 'SELECT * FROM tasks'
        );
        $expectedTable = $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../files/expectedEdited.xml')
            ->getTable("tasks");
        $this->assertTablesEqual($expectedTable, $queryTable);

    }
}