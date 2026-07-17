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

        $this->checkGit();

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


    private function checkGit(): void
    {
        exec("git --version", $output, $code);

        if ($code === 0) {
            Console::success(implode('', $output));
        } else {
            Console::error("Git is not installed.");
            $this->passed = false;
        }
    }


    private function checkComposer(): void
    {
        exec("composer --version", $output, $code);

        if ($code === 0) {
            Console::success($output[0]);
        } else {
            Console::error("Composer is not installed.");
            $this->passed = false;
        }
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
}