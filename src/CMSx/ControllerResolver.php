<?php

namespace CMSx;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ControllerResolver implements ControllerResolverInterface
{
  /** Путь к папке с контроллерами */
  protected $path;

  /** @param $path Путь к папке с контроллерами */
  function __construct($path)
  {
    $this->path = rtrim($path, DIRECTORY_SEPARATOR);
  }

  public function getController(Request $request)
  {
    $url = new URL($request->getPathInfo());

    $one = $url->getArgument(1);
    $two = $url->getArgument(2);

    $controller = 'default';
    $action = 'index';

    if ($this->checkControllerFileExists($one)) {
      $controller = $one;
      if ($two) {
        $action = $two;
      }
    } else {
      if (!$this->checkControllerFileExists($controller)) {
        throw new HttpException(404, $controller . 'Controller.php не существует');
      }

      if ($one) {
        $action = $one;
      }
    }

    $callable = $this->createController($controller, $action, $request, $url);

    if (!is_callable($callable)) {
      throw new HttpException(404, sprintf('Метод %sAction в контроллере %sController не найден', $action, $controller));
    }

    return $callable;
  }

  public function getArguments(Request $request, $controller)
  {
    return array();
  }

  protected function createController($controller, $action, Request $request, URL $url = null)
  {
    require_once $this->getControllerFilename($controller);

    $c = $controller . 'Controller';
    $a = $action . 'Action';

    if (!class_exists($c)) {
      throw new HttpException(404, $c . ' не существует');
    }

    return array(new $c($request, $url), $a);
  }

  /** Проверка существования файла контроллера */
  protected function checkControllerFileExists($ctrl)
  {
    return is_file($this->getControllerFilename($ctrl));
  }

  /** Путь к файлу контроллера */
  protected function getControllerFilename($ctrl)
  {
    return $this->path . DIRECTORY_SEPARATOR . $ctrl . 'Controller.php';
  }
}