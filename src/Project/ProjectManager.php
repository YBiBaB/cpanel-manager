<?php

namespace Cpm\Project;

use Cpm\Filesystem\Directory;
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


        try {

            $existingConfig =
                $this->checkExistingConfiguration(
                    $configPath
                );

        } catch (RuntimeException $e) {

            return;
        }


        $this->refreshRegistration(
            $project,
            $existingConfig['projectId']
            ?? null
        );
    }

    public function refreshRegistration(
        array $project,
        ?string $projectId = null
    ): bool {

        $configPath =
            $project['projectPath']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


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

            return false;
        }


        $config =
            $this->buildProjectConfiguration(
                $project,
                $projectId
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

            return false;
        }


        try {

            $registry =
                new RegistryManager();

            $registry->registerProject(
                $config['projectId'],
                $config['projectName'],
                $project['projectPath']
            );

        } catch (RuntimeException $e) {

            Console::error(
                "Failed to register project."
            );

            return false;
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

        Console::pause();

        return true;
    }

    private function checkExistingConfiguration(
        string $configPath
    ): ?array
    {
        $configManager = new ConfigManager();

        if (!$configManager->exists($configPath)) {
            return null;
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

            throw new RuntimeException(
                "Operation cancelled."
            );
        }

        Console::line();

        return $configManager->load(
            $configPath
        );
    }

    private function buildProjectConfiguration(
        array $project,
        ?string $projectId = null
    ): array {

        $repositories = [];

        $configurator = new RepositoryConfigurator();

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

            'projectId' => $projectId ?? Uuid::generate(),

            'configVersion' => 1,

            'projectName' => $project['projectName'],

            'repositories' => $repositories,
        ];
    }

    private function validateRegistration(
        array $registryProject
    ): array {

        if (
            !isset($registryProject['path'])
            ||
            !is_dir($registryProject['path'])
        ) {

            return [
                'valid' => false,
                'reason' => 'Project path does not exist.'
            ];
        }


        $configPath =
            $registryProject['path']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


        $configManager =
            new ConfigManager();


        if (
            !$configManager->exists($configPath)
        ) {

            return [
                'valid' => false,
                'reason' => 'CPM configuration not found.'
            ];
        }


        try {

            $config =
                $configManager->load(
                    $configPath
                );

        } catch (RuntimeException $e) {

            return [
                'valid' => false,
                'reason' => 'Invalid CPM configuration.'
            ];
        }


        if (
            !isset($config['projectId'])
        ) {

            return [
                'valid' => false,
                'reason' => 'Missing project ID.'
            ];
        }


        if (
            $config['projectId']
            !==
            $registryProject['projectId']
        ) {

            return [
                'valid' => false,
                'reason' => 'Project ID mismatch.'
            ];
        }


        return [
            'valid' => true
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

    public function removeCpmDirectory(
        array $project
    ): void {

        $path =
            $project['path']
            . DIRECTORY_SEPARATOR
            . ".cpm";


        if (!is_dir($path)) {

            return;
        }

        $directory = new Directory();
        $directory ->delete(
            $path
        );
    }

    public function deleteProject(
        array $project
    ): void {

        $projectPath = $project['path'];

        if (!is_dir($projectPath)) {

            throw new RuntimeException(
                "Project directory does not exist."
            );
        }

        $directory = new Directory();
        $directory->delete(
            $projectPath
        );


        $registry = new RegistryManager();

        $removed =
            $registry->unregisterProject(
                $project['projectId']
            );


        if (!$removed) {

            throw new RuntimeException(
                "Failed to remove project from registry."
            );
        }
    }
}