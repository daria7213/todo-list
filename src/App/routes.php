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
$app->get('/login', 'controller.home:loginAction')->bind('login');
$app->get('/account', 'controller.home:accountAction')->bind('account');
$app->match('/register', 'controller.home:registerAction')->bind('register');

$app->get('/tasks', 'controller.task:showAction')->bind('tasks');
$app->post('/tasks', 'controller.task:newAction')->bind('new_task');
$app->delete('/tasks', 'controller.task:deleteAction')->bind('delete_task');
$app->put('/tasks', 'controller.task:updateAction')->bind('update_task');

