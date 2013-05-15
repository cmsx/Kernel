<?php

require_once __DIR__ . '/../init.php';

use CMSx\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolverTest extends PHPUnit_Framework_TestCase
{
  /** @dataProvider dataGoodRoutes */
  function testGoodRoutes($url, $controller, $action, $result = null)
  {
    $req = Request::create($url);

    $resolver = new ControllerResolver(CTRL_PATH);
    $arr = $resolver->getController($req);

    $this->assertInstanceOf($controller, $arr[0], $url . ' неверный класс контроллера');
    $this->assertEquals($action, $arr[1], $url . ' неверный экшн');

    if (!is_null($result)) {
      $this->assertEquals($result, call_user_func($arr), $url . ' неверный результат выполнения');
    }
  }

  function dataGoodRoutes()
  {
    return array(
      // $url, $controller, $action, $result
      array('/', 'defaultController', 'indexAction'),
      array('/test/', 'defaultController', 'testAction'),
      array('/some/', 'someController', 'indexAction'),
      array('/some/test/', 'someController', 'testAction', 'some_test'),
      array('/some/magic/', 'someController', 'magicAction', 'some_magic'),
    );
  }

  /** @dataProvider dataBadRoutes */
  function testBadRoutes($url, $exception, $status = null)
  {
    $req = Request::create($url);

    $resolver = new ControllerResolver(CTRL_PATH);

    try {
      $resolver->getController($req);
      $this->fail($url . ' должен быть Exception');
    } catch (\Exception $e) {
      $this->assertInstanceOf($exception, $e, $url . ' Неверный тип исключения');
    }

    if ($status) {
      /** @var $e \Symfony\Component\HttpKernel\Exception\HttpException */
      $this->assertEquals($status, $e->getStatusCode(), 'Неверный статус');
    }
  }

  function dataBadRoutes()
  {
    return array(
      // $url, $exception, $status
      array('/not_exists/', 'Symfony\Component\HttpKernel\Exception\HttpException', 404),
      array('/bad/', 'Symfony\Component\HttpKernel\Exception\HttpException', 404),
    );
  }
}