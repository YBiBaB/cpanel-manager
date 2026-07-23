<?php

namespace Cpm\UI;

use Cpm\Config\ConfigManager;
use Cpm\Project\RepositorySelector;
use RuntimeException;

class OpenProjectMenu
{
    public function show(array $project): void
    {
        $configPath =
            $project['path']
            . DIRECTORY_SEPARATOR
            . '.cpm'
            . DIRECTORY_SEPARATOR
            . 'config.json';

        try {

            $config =
                (new ConfigManager())
                    ->load($configPath);

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }

        while (true) {

            Console::line("");

            Console::title(
                "Open: "
                . $project['projectName']
            );

            $selector =
                new RepositorySelector();

            $repository =
                $selector->select($config);

            if ($repository === null) {
                return;
            }

            $menu =
                new RepositoryMenu();

            $menu->show(
                $project,
                $repository
            );
        }
    }
}