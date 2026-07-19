<?php

namespace Cpm\System;

use Cpm\UI\Console;

class SystemCheck
{
    private bool $passed = true;

    public function check(): bool
    {
        Console::title("System Check");

        $this->checkPhp();

        $this->checkApache();

        $this->checkDatabase();

        $this->checkGit();

        $this->checkGitCredential();

        $this->checkComposer();

        $this->checkExtensions();

        return $this->passed;
    }

    private function checkPhp(): void
    {
        $version = PHP_VERSION;

        if (version_compare($version, '8.0.0', '>=')) {
            Console::success("PHP {$version}");
        } else {
            Console::error("PHP version {$version} is too old.");
            $this->passed = false;
        }
    }

    private function checkApache(): void
    {
        $result = $this->checkCommand(
            "apache2 -v",
            "Apache is not installed.",
            false,
            true
        );


        if ($result !== null) {
            return;
        }


        $result = $this->checkCommand(
            "httpd -v",
            "Apache is not installed.",
            false,
            true
        );


        if ($result !== null) {
            return;
        }


        Console::warning(
            "Apache is not installed."
        );
    }

    private function checkDatabase(): void
    {
        $this->checkCommand(
            "mysql --version",
            "MySQL/MariaDB client is not installed.",
            false
        );
    }

    private function checkGit(): void
    {
        $commandRunner = new CommandRunner();

        $result = $commandRunner->run(
            "git --version"
        );

        if ($result['success']) {

            Console::success(
                implode(
                    "",
                    $result['output']
                )
            );

            return;
        }

        Console::error(
            "Git is not installed."
        );

        $this->passed = false;
    }

    private function checkGitCredential(): void
    {
        $commandRunner = new CommandRunner();

        // Check current effective credential helper
        $result = $commandRunner->run(
            "git config --get credential.helper"
        );

        if (
            $result['success']
            && !empty($result['output'])
        ) {

            Console::success(
                "Git credential helper: "
                . $result['output'][0]
            );

            return;
        }

        Console::warning(
            "Git credential helper is not configured."
        );

        if (
            !Console::confirm(
                "Configure credential.helper store?"
            )
        ) {
            return;
        }

        // Configure global helper
        $result = $commandRunner->run(
            "git config --global credential.helper store"
        );

        if (!$result['success']) {

            Console::error(
                "Failed to configure Git credential helper."
            );

            return;
        }

        // Verify effective configuration
        $result = $commandRunner->run(
            "git config --get credential.helper"
        );

        if (
            $result['success']
            && !empty($result['output'])
        ) {

            Console::success(
                "Git credential helper: "
                . $result['output'][0]
            );

            return;
        }

        Console::warning(
            "Git credential helper configuration could not be verified."
        );
    }

    private function checkComposer(): void
    {
        $this->checkCommand(
            "composer --version",
            "Composer is not installed."
        );
    }


    private function checkExtensions(): void
    {
        $extensions = [
            'mbstring',
            'intl',
            'openssl'
        ];

        foreach ($extensions as $extension) {

            if (extension_loaded($extension)) {
                Console::success("Extension: {$extension}");
            } else {
                Console::error("Missing extension: {$extension}");
                $this->passed = false;
            }
        }
    }

    private function checkCommand(
        string $command,
        string $errorMessage,
        bool $required = true,
        bool $silent = false
    ): ?array {

        $commandRunner = new CommandRunner();

        $result = $commandRunner->run(
            $command
        );


        if ($result['success']) {

            Console::success(
                $result['output'][0]
            );

            return $result;
        }


        if (!$silent) {

            if ($required) {

                Console::error(
                    $errorMessage
                );

                $this->passed = false;

            } else {

                Console::warning(
                    $errorMessage
                );
            }
        }


        return null;
    }
}