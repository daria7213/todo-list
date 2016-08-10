<?php

use App\Controller\HomeController;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Request;
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
//        'login_path' => array(
//            'pattern' => '^/login$',
//            'anonymous' => true
//        ),
//        'register_path' => array(
//            'pattern' => '^/register$',
//            'anonymous' => true
//        ),
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
//
//$metadata = $app['validator.mapping.class_metadata_factory']->getMetadataFor('App\Entity\User');
//$metadata->addPropertyConstraint('username', new Assert\NotBlank());
//$metadata->addPropertyConstraint('username', new Assert\Length(array('min' => 3)));
//$metadata->addPropertyConstraint('email', new Assert\Email());
//$metadata->addPropertyConstraint('password', new Assert\Length(array('min' => 6)));

//$schema = $app['db']->getSchemaManager();
//if (!$schema->tablesExist('users')) {
//    $users = new Table('users');
//    $users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
//    $users->setPrimaryKey(array('id'));
//    $users->addColumn('username', 'string', array('length' => 32));
//    $users->addColumn('email', 'string', array('length' => 64));
//    $users->addUniqueIndex(array('email'));
//    $users->addColumn('password', 'string', array('length' => 255));
//    $users->addColumn('role', 'string', array('length' => 255));
//    $users->addColumn('created_at', 'datetime');
//
//    $schema->createTable($users);
//
//    $app['db']->insert('users', array(
//        'username' => 'fabien',
//        'email' => 'lol@lol',
//        'password' => $app['security.encoder.bcrypt']->encodePassword('123456',null),
//        'role' => 'ROLE_USER',
//        'created_at' => (new DateTime())->format('Y-m-d H:i:s')
//    ));
//
//    $app['db']->insert('users', array(
//        'username' => 'admin',
//        'email' => 'heh@lol',
//        'password' => $app['security.encoder.bcrypt']->encodePassword('654321',null),
//        'role' => 'ROLE_ADMIN',
//        'created_at' => (new DateTime())->format('Y-m-d H:i:s')
//    ));
//}
