<?php
/**
 * Created by PhpStorm.
 * User: Lal
 * Date: 04.07.2016
 * Time: 20:48
 */
namespace App\Controller;

use App\Entity\User;
use App\Type\UserType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\VarDumper\VarDumper;

class HomeController {
    public function indexAction(Application $app){
        return $app['twig']->render('home.html.twig');
    }

    public function accountAction(Application $app){
        $token = $app['security.token_storage']->getToken();
        if (null !== $token) {
            $user = $token->getUser();
        } else {
            return $app->redirect('tasks');
        }

        return $app['twig']->render('account.html.twig', [
            'user' => $user
        ]);
    }

    public function loginAction(Request $request, Application $app) {
        return $app['twig']->render('login.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }

    public function registerAction(Request $request, Application $app){

        $form = $app['form.factory']->create(UserType::class, null, array('users' => $app['repository.user']));
        $form->handleRequest($request);

        if($form->isValid()){
            $result = $form->getData();
            $user = new User(null, $result['name'],$result['email'], $result['password'], 'ROLE_USER', new \DateTime('NOW'));
            $app['repository.user']->save($user);

            return $app->redirect('tasks');
        }

        return $app['twig']->render('register.html.twig', [
            'form' => $form->createView()
        ]);
    }
//    public function showUsersAction(Application $app) {
//        $query = "SELECT * FROM users";
//        $users = $app['db']->fetchAll($query);
//
//        return $app['twig']->render('users.html.twig', [
//           'users' => $users
//        ]);
//    }
}