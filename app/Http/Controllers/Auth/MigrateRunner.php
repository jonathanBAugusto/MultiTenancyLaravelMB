<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MigrateRunner
{
    public function createConfig($base_name, $type = 'mysql')
    {
        switch ($type) {
            case 'mysql':
                $host = env('DB_HOST', '127.0.0.1');
                $port = env('DB_PORT', '3306');
                $username = env('DB_USERNAME', 'root');
                $password = env('DB_PASSWORD', '');
                config(["database.connections.$base_name" => [
                    // fill with dynamic data:
                    "driver" => "mysql",
                    "host" => "{$host}",
                    "port" => "{$port}",
                    "database" => "{$base_name}",
                    "username" => "{$username}",
                    "password" => "{$password}",
                    "charset" => "utf8",
                    "collation" => "utf8_unicode_ci",
                    "prefix" => "",
                    "strict" => true,
                    "engine" => null
                ]]);
                break;
        }
    }
    public function run($base_name)
    {
        Artisan::call('migrate', ['--database' => $base_name]);
    }
}
