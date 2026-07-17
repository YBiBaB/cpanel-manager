<?php

namespace Cpm\Config;

class ConfigManager
{
    public function save(
        string $projectPath,
        array $config
    ): void {

        $configDirectory =
            $projectPath . DIRECTORY_SEPARATOR . ".cpm";


        if (!is_dir($configDirectory)) {
            mkdir(
                $configDirectory,
                0755,
                true
            );
        }


        $file =
            $configDirectory .
            DIRECTORY_SEPARATOR .
            "config.json";


        file_put_contents(
            $file,
            json_encode(
                $config,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            )
        );
    }
}