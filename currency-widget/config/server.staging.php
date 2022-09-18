<?php

return [
    'database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '123456',
        'dbname' => 'phalcon',
        'charset' => 'utf8',
    ],
    'log_database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '123456',
        'dbname' => 'phalcon_log',
        'charset' => 'utf8',
    ],
    'authentication' => [
        'secret' => 'your secret key to SIGN token',
        'encryption_key' => 'Your ultra secret key to ENCRYPT the token',
        'expiration_time' => 86400 * 7,
        'iss' => 'myproject',
        'aud' => 'myproject',
    ],
];
