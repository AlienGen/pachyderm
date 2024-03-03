<?php

namespace Pachyderm;

class Container {
    public function __get($name) {
        return Service::get($name);
    }
}
