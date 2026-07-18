<?php

namespace Cpm\Project;

use Cpm\UI\Console;
use RuntimeException;
use Cpm\Config\ConfigManager;

class ProjectManager
{
    public function save(
        string $filePath,
        array $config
    ): void {

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

        $this->showScanResult($project);

        if (!Console::confirm("Continue")) {
            Console::info("Operation cancelled.");

            return;
        }

        $repositories = [];

        $configurator = new RepositoryConfigurator();

        foreach ($project['repositories'] as $repository) {

            $repositories[] = $configurator->configure($repository);
        }

        $config = [
            'version' => 1,
            'projectName' => $project['projectName'],
            'repositories' => $repositories,
        ];

        $configPath =
            $project['projectPath']
            . DIRECTORY_SEPARATOR
            . '.cpm'
            . DIRECTORY_SEPARATOR
            . 'config.json';

        try {

            $configManager = new ConfigManager();

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