<?php

class Logger {

    /**
     * Primary
     * @param string $message
     */
    public static function primary(string $message): void {
        echo "\033[0m[Arnelify POD]: $message\033[0m" . PHP_EOL;
    }

    /**
     * Success
     * @param string $message
     */
    public static function success(string $message): void {
        echo "\033[32m[Arnelify POD]: $message\033[0m" . PHP_EOL;
    }

    /**
     * Warning
     * @param string $message
     */
    public static function warning(string $message): void {
        echo "\033[33m[Arnelify POD]: $message\033[0m" . PHP_EOL;
    }

    /**
     * Danger
     * @param string $message
     */
    public static function danger(string $message): void {
        echo "\033[31m[Arnelify POD]: $message\033[0m" . PHP_EOL;        
    }
}

?>
