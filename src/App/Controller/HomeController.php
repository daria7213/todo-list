<?php
/**
 * Created by PhpStorm.
 * User: Lal
 * Date: 04.07.2016
 * Time: 20:48
 */
namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController {
    public function indexAction(Application $app){
        return $app['twig']->render('home.html.twig');
    }

    public function accountAction(Application $app){
        $token = $app['security.token_storage']->getToken();
        if (null !== $token) {
            $user = $token->getUser();
        } else {
            return $app->redirect('home');
        }

        return $app['twig']->render('account.html.twig', [
            'user' => $user
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