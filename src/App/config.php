<?php

use App\Controller\HomeController;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Validator\Constraints as Assert;

$app = new Silex\Application();

//controllers
$app['controller.home'] = function(){
    return new HomeController();
};
$app['controller.task'] = function(){
    return new TaskController();
};

//repositories
$app['repository.user'] = function($app){
    return new UserRepository($app['db'], $app['security.encoder.bcrypt'], $app['validator']);
};
$app['repository.task'] = function($app){
    return new TaskRepository($app['db']);
};

//services
$app->register(new Silex\Provider\VarDumperServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.form.templates' => array('form_div_layout.html.twig', 'twig/form_div_layout.html.twig'),
    'twig.path' => __DIR__ . '\views',
));
$app->register(new Silex\Provider\SerializerServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'en',
    'locale_fallbacks' => array('ru'),
));
$app->extend('translator', function($translator, $app) {
    $translator->addResource('xliff', __DIR__.'/locales/en.xlf', 'en');
    $translator->addResource('xliff', __DIR__.'/locales/ru.xlf', 'ru');
    return $translator;
});
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'todo',
        'user'      => 'root',
        'password'  => '1723',
    ),
));
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/.*$',
            'anonymous' => true,
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/login_check',
            ),
            'logout' => array(
                'logout_path' => '/logout',
                'invalidate_session' => false
            ),
            'users' => function () use ($app) {
                return $app['repository.user'];
            }
        ),
    ),
    'security.access_rules' => array(
        array('^/login$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
        array('^/register$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
        array('^/.+$', array('ROLE_ADMIN', 'ROLE_USER')),
    )
));
$app->register(new FormServiceProvider());

$app->register(new Silex\Provider\ValidatorServiceProvider());