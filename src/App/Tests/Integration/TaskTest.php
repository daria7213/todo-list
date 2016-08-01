<?php
/**
 * Created by PhpStorm.
 * User: Lal
 * Date: 20.07.2016
 * Time: 6:38
 */
namespace App\Tests;

use Silex\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TestTask extends WebTestCase {

    public function createApplication()
    {
        $app = require __DIR__.'/../../app.php';
        $app['session.test'] = true;
        return $app;
    }

    public function testHomepage(){
        $client = $this->createClient();

        $client->request('GET', '/tasks');
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());

        $this->assertCount(1, $crawler->filter("h1:contains('Home')"));
    }

    public function login(){

        $client = $this->createClient();
        $session  = $this->createApplication()['session'];

        $firewall = 'default';

        $token = new UsernamePasswordToken('admin', null, $firewall,array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    public function testShow(){
        $client = $this->login();


    }

    public function testInvalidLogin(){
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/login');
        $this->assertCount(1, $crawler->filter('form'));

        $form = $crawler->selectButton('Submit')->form();
        $crawler = $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegexp(
            '/Invalid username or password/',
            $client->getResponse()->getContent()
        );

        $crawler = $client->submit($form, array(
            '_username' => 'admin',
            '_password' => '654321'
        ));
        $this->assertEquals('Home',$crawler->filter('title')->text());
    }

    public function testLogout(){
        $client = $this->login();

        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertRegexp(
            '/Logout/',
            $client->getResponse()->getContent()
        );

    }
}