<?php
namespace App\Core\Storage;

use App\Config\Database as DBConfig;
use App\Core\Interfaces\SingletonInterface;

// using "Singleton" pattern
final class MysqlPdo implements SingletonInterface
{
    private static $instance = null;
    
    private function __construct()
    {
    }
    
    public static function getInstance(): ?object
    {
        try {
            return self::$instance === null ? 
                self::$instance = new \PDO(
                    "mysql:host=" . DBConfig::HOST . ";dbname=" . DBConfig::NAME,
                    DBConfig::USER,
                    DBConfig::PASSWORD
                ) : 
                self::$instance;
        } catch (\Exception $err) {
            return null;
        }
    }
}
