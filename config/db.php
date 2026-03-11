<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . (getenv('DB_HOST') ?: 'db') . ';dbname=' . (getenv('DB_NAME') ?: 'finance'),
    'username' => getenv('DB_USER') ?: 'finance_user',
    'password' => getenv('DB_PASSWORD') ?: 'finance_pass',
    'charset' => 'utf8mb4',
    'enableSchemaCache' => !YII_ENV_DEV,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
