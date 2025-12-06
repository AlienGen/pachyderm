<?php

namespace Pachyderm;

class Container {
    public function __get(string $name): mixed {
        return Service::get($name);
    }
}
