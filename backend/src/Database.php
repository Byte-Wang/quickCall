<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $config = require dirname(__DIR__) . '/config.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['name'],
                $config['charset']
            );

            self::$pdo = new PDO($dsn, $config['user'], $config['pass']);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            self::migrate();
        }

        return self::$pdo;
    }

    private static function migrate(): void
    {
        $sql = (string)file_get_contents(dirname(__DIR__) . '/database/schema.sql');

        // 过滤注释行，再按分号拆分执行
        $lines = array_filter(
            explode("\n", $sql),
            fn(string $line): bool => !str_starts_with(trim($line), '--')
        );
        $sql = implode("\n", $lines);

        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            self::$pdo->exec($statement);
        }
    }
}
