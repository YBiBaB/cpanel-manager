<?php

namespace Cpm\Project;

use Cpm\UI\Console;
use RuntimeException;
use Cpm\Config\ConfigManager;
use Cpm\Registry\RegistryManager;
use Cpm\Utils\Uuid;

class ProjectManager
{
    public function addExistingProject(): void
    {
        Console::title(
            "Add Existing Project"
        );


        $projectPath = Console::ask(
            "Project path"
        );


        try {

            $scanner = new ProjectScanner();

            $project =
                $scanner->scan(
                    $projectPath
                );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            return;
        }


        $configPath =
            $project['projectPath']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


        if (
            !$this->checkExistingConfiguration(
                $configPath
            )
        ) {
            return;
        }


        $this->showScanResult(
            $project
        );


        if (
            !Console::confirm(
                "Continue"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return;
        }


        $config =
            $this->buildProjectConfiguration(
                $project
            );


        try {

            $configManager =
                new ConfigManager();

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


        try {

            $registry =
                new RegistryManager();

            $registry->addProject(
                $config['projectId'],
                $config['projectName'],
                $project['projectPath']
            );

        } catch (RuntimeException $e) {

            Console::error(
                "Failed to register project."
            );

            return;
        }


        Console::separator();

        Console::success(
            "Project configuration saved."
        );

        Console::success(
            "Project registered successfully."
        );

        Console::line();


        $this->showProjectSummary(
            $config,
            $configPath
        );
    }

    private function checkExistingConfiguration(
        string $configPath
    ): bool {

        $configManager = new ConfigManager();


        if (!$configManager->exists($configPath)) {
            return true;
        }


        Console::info(
            "Project configuration already exists."
        );


        if (
            !Console::confirm(
                "Overwrite existing configuration"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return false;
        }


        Console::line();

        return true;
    }

    private function buildProjectConfiguration(
        array $project
    ): array {

        $repositories = [];


        $configurator =
            new RepositoryConfigurator();


        foreach (
            $project['repositories']
            as $repository
        ) {

            $repositories[] =
                $configurator->configure(
                    $repository
                );
        }


        return [

            'projectId' =>
                Uuid::generate(),

            'configVersion' => 1,

            'projectName' =>
                $project['projectName'],

            'repositories' =>
                $repositories,
        ];
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