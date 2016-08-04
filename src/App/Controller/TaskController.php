<?php
/**
 * Created by PhpStorm.
 * User: Lal
 * Date: 09.07.2016
 * Time: 4:12
 */
namespace App\Controller;

use App\Entity\Task;
use DateTime;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;
class TaskController
{
    public function showAction(Application $app){
        $token = $app['security.token_storage']->getToken();
        if (null !== $token) {
            $user = $token->getUser();
        } else {
            return $app->redirect('home');
        }

        $tasks = $app['repository.task']->findAllByUserId($user->getId());
        return $app['twig']->render('tasks.html.twig',[
            'tasks' => $tasks
        ]);
    }

    public function newAction(Request $request, Application $app){

        $task = new Task(
            null,
            $app['security.token_storage']->getToken()->getUser()->getId(),
            $request->request->get('text'),
            $request->request->get('priority') == 'true' ? true : false,
            DateTime::createFromFormat('Y-m-d', $request->request->get('date')),
            $request->request->get('status') == 'true' ? true : false,
            new \DateTime('NOW')
        );

        $app['repository.task']->save($task);

        $taskData = array(
            'id' => $task->getId(),
            'text' => $task->getText(),
            'date' => $task->getDateString(),
            'priority' => $task->getPriority(),
            'status' => $task->getStatus()
        );
        //return '<pre>'.var_dump($task).'</pre><br><pre>'.var_dump($result).'</pre>';
        return $app->json(json_encode($taskData));
    }

    public function deleteAction(Request $request, Application $app){

        $id = $request->request->get('id');
        $app['repository.task']->delete($id);

        return new Response();

    }

    public function updateAction(Request $request, Application $app){
        $task = new Task(
            $request->request->get('id'),
            $app['security.token_storage']->getToken()->getUser()->getId(),
            $request->request->get('text'),
            $request->request->get('priority') == 'true' ? true : false,
            DateTime::createFromFormat('Y-m-d', $request->request->get('date')),
            $request->request->get('status') == 'true' ? true : false
        );

        $app['repository.task']->update($task);

        return new Response();
    }
}