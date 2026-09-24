<?php

namespace Cpm\System;

use RuntimeException;

class CommandRunner
{
    private ?string $workingDirectory = null;

    private array $environment = [];


    public function setWorkingDirectory(
        ?string $directory
    ): self {

        if (
            $directory !== null
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                "Working directory does not exist."
            );
        }


        $this->workingDirectory = $directory;

        return $this;
    }


    public function setEnvironment(
        array $environment
    ): self {

        $this->environment = $environment;

        return $this;
    }


    public function run(
        string $command
    ): array {

        $fullCommand = $this->buildCommand(
            $command
        );


        $output = [];

        $error = [];

        $code = 0;


        exec(
            $fullCommand . " 2>&1",
            $output,
            $code
        );


        return [
            'success' => $code === 0,

            'code' => $code,

            'output' => $output,

            'error' => $error,

            'command' => $command
        ];
    }


    public function execute(
        string $command
    ): string {

        $result = $this->run(
            $command
        );


        if (!$result['success']) {

            throw new RuntimeException(
                sprintf(
                    "Command failed (%s): %s",
                    $result['code'],
                    $command
                )
            );
        }


        return implode(
            PHP_EOL,
            $result['output']
        );
    }


    private function buildCommand(
        string $command
    ): string {

        $parts = [];


        if ($this->workingDirectory !== null) {

            $parts[] =
                "cd " .
                escapeshellarg(
                    $this->workingDirectory
                )
                .
                " && ";
        }


        if (!empty($this->environment)) {

            foreach (
                $this->environment as $key => $value
            ) {

                $parts[] =
                    $key .
                    "=" .
                    escapeshellarg($value)
                    .
                    " ";
            }
        }


        $parts[] = $command;


        return implode(
            "",
            $parts
        );
    }
}