<?php

namespace Pachyderm;

interface MiddlewareInterface {
  public function handleBeforeRequest();

  public function handleAfterRequest();
}