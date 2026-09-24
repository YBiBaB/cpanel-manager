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
            ?? null,
            $existingConfig['projectName']
            ?? null
        );
    }

    public function refreshRegistration(
        array $project,
        ?string $projectId = null,
        ?string $projectName = null
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
                $projectId,
                $projectName
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
        ?string $projectId = null,
        ?string $projectName = null
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

            'projectName' =>
                $projectName
                ?? $project['projectName'],

            'repositories' => $repositories,
        ];
    }

    public function renameProject(
        array $project,
        string $newName
    ): void {

        $configPath =
            $project['path']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


        $configManager = new ConfigManager();


        if (!$configManager->exists($configPath)) {

            throw new RuntimeException(
                "CPM configuration not found."
            );
        }


        $config =
            $configManager->load(
                $configPath
            );


        $config['projectName'] = $newName;


        $configManager->save(
            $configPath,
            $config
        );


        $registry = new RegistryManager();

        $registry->registerProject(
            $project['projectId'],
            $newName,
            $project['path']
        );
    }

    public function repairPath(
        array $project,
        string $newPath
    ): array {

        $resolvedPath = realpath($newPath);


        if (
            $resolvedPath === false
            || !is_dir($resolvedPath)
        ) {

            throw new RuntimeException(
                "Project path does not exist."
            );
        }


        if (!is_readable($resolvedPath)) {

            throw new RuntimeException(
                "Project path is not readable."
            );
        }


        $configPath =
            $resolvedPath
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


        $configManager = new ConfigManager();


        if (!$configManager->exists($configPath)) {

            throw new RuntimeException(
                "CPM configuration not found."
            );
        }


        $config =
            $configManager->load(
                $configPath
            );


        if (
            !isset($config['projectId'])
        ) {

            throw new RuntimeException(
                "Missing project ID."
            );
        }


        if (
            $config['projectId']
            !==
            $project['projectId']
        ) {

            throw new RuntimeException(
                "Project ID mismatch."
            );
        }


        $repositories =
            $config['repositories'] ?? [];


        foreach (
            $repositories
            as $index => $repository
        ) {

            if (
                !isset($repository['folder'])
            ) {

                throw new RuntimeException(
                    "Repository folder is missing in configuration."
                );
            }


            $repositoryPath =
                $resolvedPath
                . DIRECTORY_SEPARATOR
                . $repository['folder'];


            $resolvedRepositoryPath =
                realpath($repositoryPath);


            if (
                $resolvedRepositoryPath === false
                || !is_dir($resolvedRepositoryPath)
            ) {

                throw new RuntimeException(
                    "Repository path does not exist: "
                    . $repository['folder']
                );
            }


            $repositories[$index]['path'] =
                $resolvedRepositoryPath;
        }


        $config['repositories'] =
            $repositories;


        $configManager->save(
            $configPath,
            $config
        );


        $projectName =
            $config['projectName']
            ?? $project['projectName'];


        $registry = new RegistryManager();

        $registry->registerProject(
            $project['projectId'],
            $projectName,
            $resolvedPath
        );


        return [

            'projectId' => $project['projectId'],

            'projectName' => $projectName,

            'path' => $resolvedPath,

        ];
    }

    public function validateRegistration(
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

    public function prepareBranchDirectory(
        array $project,
        string $branchName,
        string $folder,
        string $remoteUrl
    ): string {

        if (
            !isset($project['path'])
            || !is_dir($project['path'])
        ) {

            throw new RuntimeException(
                "Project path does not exist."
            );
        }


        $targetPath =
            $project['path']
            . DIRECTORY_SEPARATOR
            . $folder;


        $gitHelper = new GitHelper();


        if (is_dir($targetPath)) {

            if (
                !is_dir(
                    $targetPath
                    . DIRECTORY_SEPARATOR
                    . '.git'
                )
            ) {

                throw new RuntimeException(
                    "Directory exists but is not a git repository: "
                    . $folder
                );
            }


            $gitHelper->pull(
                $targetPath
            );


            $resolved = realpath($targetPath);


            if ($resolved === false) {

                throw new RuntimeException(
                    "Cannot resolve branch path."
                );
            }


            return $resolved;
        }


        $gitHelper->cloneBranch(
            $remoteUrl,
            $branchName,
            $targetPath
        );


        $gitHelper->pull(
            $targetPath
        );


        $resolved = realpath($targetPath);


        if ($resolved === false) {

            throw new RuntimeException(
                "Cannot resolve branch path."
            );
        }


        return $resolved;
    }

    public function appendRepository(
        array $project,
        array $repository
    ): void {

        $configPath =
            $this->getConfigPath(
                $project
            );


        $configManager =
            new ConfigManager();

        $config =
            $configManager->load(
                $configPath
            );


        $config['repositories'][] =
            $repository;


        $configManager->save(
            $configPath,
            $config
        );
    }

    public function removeBranchRegistration(
        array $project,
        int $repositoryIndex
    ): void {

        $configPath =
            $this->getConfigPath(
                $project
            );


        $configManager =
            new ConfigManager();

        $config =
            $configManager->load(
                $configPath
            );


        $repositories =
            $config['repositories'] ?? [];


        if (
            !isset(
                $repositories[$repositoryIndex]
            )
        ) {

            throw new RuntimeException(
                "Repository not found."
            );
        }


        unset(
            $repositories[$repositoryIndex]
        );


        $config['repositories'] =
            array_values(
                $repositories
            );


        $configManager->save(
            $configPath,
            $config
        );
    }

    public function deleteBranch(
        array $project,
        int $repositoryIndex
    ): void {

        $configPath =
            $this->getConfigPath(
                $project
            );


        $configManager =
            new ConfigManager();

        $config =
            $configManager->load(
                $configPath
            );


        $repositories =
            $config['repositories'] ?? [];


        if (
            !isset(
                $repositories[$repositoryIndex]
            )
        ) {

            throw new RuntimeException(
                "Repository not found."
            );
        }


        $repository =
            $repositories[$repositoryIndex];

        $repositoryPath =
            $repository['path'] ?? null;


        if (
            $repositoryPath !== null
            && is_dir($repositoryPath)
        ) {

            $directory = new Directory();
            $directory->delete(
                $repositoryPath
            );
        }


        unset(
            $repositories[$repositoryIndex]
        );


        $config['repositories'] =
            array_values(
                $repositories
            );


        $configManager->save(
            $configPath,
            $config
        );
    }

    private function getConfigPath(
        array $project
    ): string {

        if (
            !isset($project['path'])
            || $project['path'] === ''
        ) {

            throw new RuntimeException(
                "Project path does not exist."
            );
        }


        return $project['path']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";
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