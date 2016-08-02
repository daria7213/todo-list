<?php
/**
 * Created by PhpStorm.
 * User: Lal
 * Date: 04.07.2016
 * Time: 20:50
 */

use App\Controller\HomeController;
use App\Controller\TaskController;
use Symfony\Component\HttpFoundation\Request;

$app->get('/', "controller.home:indexAction")->bind('home');
$app->get('/hello/{name}', 'controller.home:helloAction')->bind('hello');
$app->get('/users', 'controller.home:showUsersAction');
$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');
$app->get('/account', 'controller.home:accountAction')->bind('account');
$app->get('tasks', 'controller.task:showAction')->bind('tasks');
$app->post('/tasks', 'controller.task:newAction')->bind('new_task');
$app->delete('/tasks', 'controller.task:deleteAction')->bind('delete_task');

$app->get('/user', function() use ($app){
    $user = new \App\Entity\User(1,'doo','heheheh','lol','ROLE_ADMIN',null);
    $errors = $app['validator']->validate($user);

    if(is_a($app['validator'], 'Symfony\Component\Validator\Validator\RecursiveValidator')){
        echo 'The author is valid';
    } else {
        echo get_class($app['validator']);
    }

});