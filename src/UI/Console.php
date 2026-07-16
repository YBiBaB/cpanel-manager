<?php

namespace Cpm\UI;

class Console
{
    public static function write(string $message): void
    {
        echo $message;
    }

    public static function line(string $message = ''): void
    {
        echo $message . PHP_EOL;
    }

    public static function success(string $message): void
    {
        self::line("[OK] " . $message);
    }

    public static function warning(string $message): void
    {
        self::line("[WARNING] " . $message);
    }

    public static function error(string $message): void
    {
        self::line("[ERROR] " . $message);
    }

    public static function title(string $title): void
    {
        self::line();
        self::line("========================================");
        self::line($title);
        self::line("========================================");
    }

    public static function ask(
    string $question,
    bool $allowEmpty = false,
    ?callable $validator = null,
    ?string $errorMessage = null
): string {
    while (true) {
        self::write($question . " ");

        $input = trim(fgets(STDIN));

        // Check if input is empty
        if (!$allowEmpty && $input === '') {
            self::error("Input cannot be empty.");
            continue;
        }

        // Custom validation
        if ($validator !== null && !$validator($input)) {
            self::error($errorMessage ?? "Invalid input.");
            continue;
        }

        return $input;
    }
}
}