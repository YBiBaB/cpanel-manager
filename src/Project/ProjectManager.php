<?php

namespace Cpm\Project;

use Cpm\UI\Console;
use RuntimeException;
use Cpm\Config\ConfigManager;
use Cpm\Utils\Uuid;

class ProjectManager
{
    public function addExistingProject(): void
    {
        Console::title("Add Existing Project");


        $projectPath = Console::ask(
            "Project path"
        );


        try {

            $scanner = new ProjectScanner();

            $project = $scanner->scan($projectPath);


        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            return;
        }


        /*
         * Generate config path
         */
        $configPath =
            $project['projectPath']
            . DIRECTORY_SEPARATOR
            . '.cpm'
            . DIRECTORY_SEPARATOR
            . 'config.json';


        /*
         * Check existing configuration
         */
        $configManager = new ConfigManager();


        if ($configManager->exists($configPath)) {

            Console::info(
                "Project configuration already exists."
            );


            if (!Console::confirm("Reconfigure project")) {

                Console::info(
                    "Operation cancelled."
                );

                return;
            }


            Console::info(
                "Existing configuration will be overwritten."
            );

            Console::line();
        }


        /*
         * Show scan result
         */
        $this->showScanResult($project);


        if (!Console::confirm("Continue")) {

            Console::info(
                "Operation cancelled."
            );

            return;
        }


        /*
         * Configure repositories
         */
        $repositories = [];


        $configurator = new RepositoryConfigurator();


        foreach ($project['repositories'] as $repository) {

            $repositories[] =
                $configurator->configure($repository);

        }


        /*
         * Build configuration
         */
        $config = [
            'projectId' => Uuid::generate(),

            'configVersion' => 1,

            'projectName' => $project['projectName'],

            'repositories' => $repositories,
        ];

        /*
         * Config file overwrite confirmation
         */

        if ($configManager->exists($configPath)) {

            if (!Console::confirm("Overwrite existing configuration")) {

                Console::info(
                    "Operation cancelled."
                );

                return;
            }
        }


        /*
         * Save configuration
         */
        try {

            $configManager->save(
                $configPath,
                $config
            );


        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            return;
        }


        Console::separator();


        Console::success(
            "Project configuration saved."
        );


        Console::line();


        $this->showProjectSummary(
            $config,
            $configPath
        );
    }

    private function showScanResult(array $project): void
    {
        Console::success(
            "Project found: " .
            $project['projectName']
        );

        Console::success(
            "Found " .
            count($project['repositories']) .
            " repositories."
        );

        Console::line();

        Console::info(
            "Repositories:"
        );

        foreach ($project['repositories'] as $repository) {

            Console::line(
                "- " .
                $repository['folder']
            );
        }

        Console::line();
    }

    private function showProjectSummary(
        array $config,
        string $configPath
    ): void {

        Console::title(
            "Project Summary"
        );

        Console::line(
            "\nProject Name: " .
            $config['projectName']
        );

        Console::line(
            "Project ID: " .
            $config['projectId']
        );

        Console::line(
            "Config Version: " .
            $config['configVersion']
        );

        Console::line();

        foreach ($config['repositories'] as $repository) {

            Console::separator();

            Console::line(
                "Repository: " .
                $repository['name']
            );

            Console::line(
                "Branch: " .
                $repository['branch']
            );

            Console::line(
                "Domain: " .
                $repository['domain']
            );

            Console::line(
                "Document Root: " .
                $repository['documentRoot']
            );

            Console::line(
                "Path: " .
                $repository['path']
            );

            Console::line();
        }

        Console::separator();

        Console::line(
            "Config: " .
            $configPath
        );

        Console::line();
    }

}