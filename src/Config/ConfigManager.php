<?php

namespace Cpm\Config;

use RuntimeException;

class ConfigManager
{
    public function save(
        string $filePath,
        array $config
    ): void
    {
        $directory = dirname($filePath);

        if (!is_dir($directory)) {

            if (!mkdir($directory, 0755, true)) {
                throw new RuntimeException(
                    "Cannot create config directory."
                );
            }
        }

        $result = file_put_contents(
            $filePath,
            json_encode(
                $config,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            )
        );

        if ($result === false) {
            throw new RuntimeException(
                "Cannot write config file."
            );
        }
    }

    public function load(
        string $filePath
    ): array {

        if (!file_exists($filePath)) {
            throw new RuntimeException(
                "Config file not found."
            );
        }


        $content = file_get_contents($filePath);


        if ($content === false) {
            throw new RuntimeException(
                "Cannot read config file."
            );
        }


        $config = json_decode(
            $content,
            true
        );


        if (!is_array($config)) {
            throw new RuntimeException(
                "Invalid config file."
            );
        }


        return $config;
    }


    public function exists(
        string $filePath
    ): bool {

        return file_exists($filePath);
    }
}