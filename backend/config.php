<?php

declare(strict_types=1);

// 数据库配置（可通过环境变量覆盖）
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'quick_call',
    'user' => getenv('DB_USER') ?: 'quick_call',
    'pass' => getenv('DB_PASS') ?: 'AiEjxXhtxkfWxx26',
    'charset' => 'utf8mb4',
];
