<?php

namespace CMSx;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EventListener implements EventSubscriberInterface
{
  protected $error_path = '/error/';

  /** @param $error_path - Путь куда будет сделан подзапрос для отображения Exception. По-умолчанию /error/ */
  function __construct($error_path = null)
  {
    if (!is_null($error_path)) {
      $this->error_path = $error_path;
    }
  }

  // Список событий на которые подписываемся
  public static function getSubscribedEvents()
  {
    return array(
      KernelEvents::EXCEPTION => 'onException',
      KernelEvents::VIEW      => 'onView'
    );
  }

  // Обработчик при возникновении исключений
  public function onException(GetResponseForExceptionEvent $event)
  {
    $request = Request::create($this->error_path);
    $request->attributes->add(
      array(
        'exception' => FlattenException::create($event->getException())
      )
    );

    try {
      $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, true);
    } catch (\Exception $e) {
      return;
    }

    $event->setResponse($response);
  }

  // Обработчик, если контроллер вернул не Response
  public function onView(GetResponseForControllerResultEvent $event)
  {
    $result = $event->getControllerResult();

    if (is_object($result) && method_exists($result, 'toResponse')) {
      $response = $result->toResponse();
    } elseif (is_array($result)) {
      $response = JsonResponse::create($result);
    } else {
      $response = Response::create($result);
    }

    if ($response) {
      $event->setResponse($response);
    }
  }
}