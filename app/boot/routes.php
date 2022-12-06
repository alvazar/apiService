<?php

use App\Core\Route;
use App\Core\Request;
use App\Controllers\ApiController;
use App\Core\Storage\MysqlPdo;

Route::get(
    '/api/install',
    function ($params) {
        $db = MysqlPdo::getInstance();

        $qu = "CREATE TABLE IF NOT EXISTS sites_content ("
            . "ID INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT," 
            . "created DATETIME DEFAULT CURRENT_TIMESTAMP,"
            . "updated DATETIME DEFAULT CURRENT_TIMESTAMP,"
            . "url VARCHAR(255) DEFAULT '',"
            . "quantity INT(10) DEFAULT 0,"
            . "data TEXT,"
            . "searchType VARCHAR(10) DEFAULT NULL,"
            . "searchQuery VARCHAR(100) DEFAULT NULL"
            . ") ENGINE = InnoDB CHARACTER SET utf8";

        $db->query($qu);

        $db->query('CREATE INDEX url ON sites_content(url)');
        $db->query('CREATE INDEX searchType ON sites_content(searchType)');
        $db->query('CREATE INDEX searchQuery ON sites_content(searchQuery)');
    }
);

Route::get(
    '/api/{action}',
    function ($params) {
        print (new ApiController())
            ->send($params)
            ->getJson();
    }
);

// start routing
Route::start(new Request());
