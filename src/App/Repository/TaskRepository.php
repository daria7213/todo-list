<?php

namespace App\Repository;

use DateTime;
use Doctrine\DBAL\Driver\Connection;
use App\Entity\Task;
use Doctrine\DBAL\Driver\PDOException;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Created by PhpStorm.
 * User: Lal
 * Date: 09.07.2016
 * Time: 4:13
 */

class TaskRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function save(Task $task){
        $taskData = [
            'user_id' => $task->getUserId(),
            'text' => $task->getText(),
            'priority' => $task->getPriority(),
            'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            'status' => $task->getStatus(),
            'date' => $task->getDate()->format('Y-m-d H:i:s')
        ];

        $this->conn->insert('tasks', $taskData);
        $id = $this->conn->lastInsertId();
        $task->setId($id);
    }

    public function findAllByUserId($userId){
        $query = 'SELECT * FROM tasks WHERE user_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1,$userId);
        $stmt->execute();
        if (!$taskdata = $stmt->fetchAll()) {
            throw new Exception('Tasks not found');
        }
        $tasks = [];
        foreach($taskdata as $task){
            $tasks[] = $this->buildTask($task);
        }

        return $tasks;
    }
    public function findAll(){
        $query = 'SELECT * FROM tasks';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        if (!$taskdata = $stmt->fetchAll()) {
            throw new Exception('Tasks not found');
        }
        $tasks = [];
        foreach($taskdata as $task){
            $tasks[] = $this->buildTask($task);
        }

        return $tasks;
    }

    public function find($id) {
        $taskData = $this->conn->fetchAssoc('SELECT * FROM tasks WHERE id = ?', array($id));
        return $this->buildTask($taskData);
    }

    public function delete($id) {
        return $this->conn->delete('tasks', array('id' => $id));
    }

    public function update(Task $task) {
        return $this->conn->update('tasks',array(
            'text' => $task->getText(),
            'date' => $task->getDate()->format('Y-m-d H:i:s'),
            'priority'=> $task->getPriority(),
            'status' => $task->getStatus()
            ), array('id' => $task->getId()));
    }

    public function buildTask($task){
        return new Task(
            $task['id'],
            $task['user_id'],
            $task['text'],
            $task['priority'],
            DateTime::createFromFormat('Y-m-d H:i:s', $task['date']),
            $task['status'],
            DateTime::createFromFormat('Y-m-d H:i:s', $task['created_at'])
        );
    }
}