<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class Database {

    function __construct() {
        $capsule = new Capsule();
        $capsule->addConnection([
            "driver" => _driver_,
            "host" => _host_,
            "database" => _database_,
            "username" => _username_,
            "password" => _password_
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public function Table($table) {
        return Capsule::table($table);
    }
}
