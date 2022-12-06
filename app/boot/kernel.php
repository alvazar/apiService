<?php

use App\Core\Container;
use App\Core\Validator;
use App\Repository\DBRepository;
use App\Repository\HttpRepository;

//
Container::add('validator', Validator::class);

//
Container::add('HttpRepository', HttpRepository::class);
Container::add('DBRepository', DBRepository::class);
