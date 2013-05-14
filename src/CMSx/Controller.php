<?php

namespace CMSx;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class Controller
{
  /** @var Request */
  protected $request;

  /** @var URL */
  protected $url;

  function __construct(Request $request, URL $url = null)
  {
    $this->setRequest($request);
    $this->setUrl($url);
  }

  public function setRequest(Request $request)
  {
    $this->request = $request;

    return $this;
  }

  /** @return \Symfony\Component\HttpFoundation\Request */
  public function getRequest()
  {
    return $this->request;
  }

  public function setUrl(URL $url)
  {
    $this->url = $url;

    return $this;
  }

  /** @return \CMSx\URL */
  public function getUrl($clone = false)
  {
    if (is_null($this->url)) {
      $this->url = new URL($this->getRequest()->getPathInfo());
    }

    return $clone ? clone $this->url : $this->url;
  }

  /** Проверка сделан ли запрос через Ajax */
  public function isAjax()
  {
    return $this->getRequest()->isXmlHttpRequest();
  }

  /** Выброс исключения: страница не найдена */
  public function notFound($msg = null)
  {
    throw new HttpException(404, $msg);
  }

  /** Выброс исключения: доступ запрещен */
  public function forbidden($msg = null)
  {
    throw new HttpException(403, $msg);
  }

  /** @return RedirectResponse */
  public function redirect($url, $status = null)
  {
    return new RedirectResponse($url, $status ? : 302);
  }

  /**
   * Возврат по HTTP REFERER`у или при его отсутствии на главную
   *
   * @return RedirectResponse
   */
  public function back()
  {
    return $this->redirect($this->getRequest()->server->get('HTTP_REFERER', '/'));
  }
}