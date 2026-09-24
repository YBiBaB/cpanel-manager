<?php

namespace Cpm\Project;

use Cpm\System\CommandRunner;
use RuntimeException;

class ComposerHelper
{
    public function installProduction(
        string $path
    ): void {

        $composerJson =
            $path
            . DIRECTORY_SEPARATOR
            . 'composer.json';


        if (!file_exists($composerJson)) {

            throw new RuntimeException(
                "composer.json not found."
            );
        }


        $commandRunner = new CommandRunner();

        $commandRunner->setWorkingDirectory(
            $path
        );


        $result = $commandRunner->run(
            "composer install"
            . " --no-dev"
            . " --optimize-autoloader"
            . " --no-interaction"
        );


        if (!$result['success']) {

            throw new RuntimeException(
                "Composer install failed."
            );
        }
    }
}
