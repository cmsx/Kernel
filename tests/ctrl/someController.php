<?php

use CMSx\Controller;

class someController extends Controller
{
  function indexAction()
  {
    return 'some_index';
  }

  function testAction()
  {
    return 'some_test';
  }

  function __call($name, $arguments)
  {
    return 'some_magic';
  }
}