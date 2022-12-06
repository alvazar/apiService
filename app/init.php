<?php

use App\Config\Namespaces;
use App\Core\Loader;

//
require_once __DIR__ . '/Config/Namespaces.php';
require_once __DIR__ . '/Core/Loader.php';

// app autoload
spl_autoload_register(function ($cl) {
    (new Loader(Namespaces::NS_LIST))->start($cl);
});

// start app
require_once __DIR__ . '/boot/kernel.php';
require_once __DIR__ . '/boot/services.php';
require_once __DIR__ . '/boot/routes.php';
