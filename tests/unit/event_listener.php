<?php

require_once __DIR__ . '/../init.php';

use CMSx\EventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class EventListenerTest extends PHPUnit_Framework_TestCase
{
  /** @dataProvider dataViewEvent */
  function testViewEvent($result, $expected)
  {
    $l = new EventListener();
    $e = new GetResponseForControllerResultEvent(
      new TestEventListenerKernel(),
      Request::create('/foo'),
      HttpKernelInterface::MASTER_REQUEST,
      $result
    );

    $l->onView($e);

    $r = $e->getResponse();
    $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $r, 'Возвращен Response');
    $this->assertEquals($expected, $r->getContent(), 'Контент');
  }

  function dataViewEvent()
  {
    return array(
      array('test', 'test'),
      array(123, '123'),
      array(array('one' => 'two', 12 => NULL), '{"one":"two","12":null}'),
      array(new TestObjectToString('blabla'), 'string-blabla'),
      array(new TestObjectToResponse('blabla'), 'response-blabla'),
    );
  }

  /** @dataProvider dataExceptionEvent */
  function testExceptionEvent(\Exception $exception, $exp_message, $exp_code, $error_path = null)
  {
    $l = new EventListener($error_path);
    $e = new GetResponseForExceptionEvent(
      new TestEventListenerKernel(),
      Request::create('/foo'),
      HttpKernelInterface::MASTER_REQUEST,
      $exception
    );

    $l->onException($e);

    $r = $e->getResponse();
    $this->assertEquals($exp_message, $r->getContent(), 'Текст');
    $this->assertEquals($exp_code, $r->getStatusCode(), 'Код');
  }

  function dataExceptionEvent()
  {
    return array(
      array(new HttpException(404, 'bla-bla'), '/error/ => bla-bla', 404),
      array(new \Exception('hell-o', 666), '/somewhere/else/ => hell-o', 500, '/somewhere/else/'),
    );
  }
}

class TestEventListenerKernel implements \Symfony\Component\HttpKernel\HttpKernelInterface
{
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
  {
    $text   = 'foo';
    $status = 200;

    /** @var $e FlattenException */
    if ($type == self::SUB_REQUEST && $e = $request->attributes->get('exception')) {
      $text   = $request->getPathInfo() . ' => ' . $e->getMessage();
      $status = $e->getStatusCode();
    }

    return Response::create($text, $status);
  }
}

class TestObjectToString
{
  protected $msg;

  function __construct($msg)
  {
    $this->msg = $msg;
  }

  function __toString()
  {
    return 'string-' . $this->msg;
  }
}

class TestObjectToResponse extends TestObjectToString
{
  function toResponse()
  {
    return Response::create('response-' . $this->msg);
  }
}