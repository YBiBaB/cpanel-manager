<?php

namespace Cpm\Project;

use RuntimeException;

class ProjectScanner
{
    public function scan(string $projectPath): array
    {
        $projectPath = realpath($projectPath);

        $this->validateProjectPath($projectPath);

        return [
            'projectName' => basename($projectPath),
            'projectPath' => $projectPath,
            'repositories' => $this->scanRepositories($projectPath),
        ];
    }


    private function validateProjectPath(?string $projectPath): void
    {
        if ($projectPath === false || $projectPath === null) {
            throw new RuntimeException(
                "Project path does not exist."
            );
        }

        if (!is_dir($projectPath)) {
            throw new RuntimeException(
                "Project path is not a directory."
            );
        }

        if (!is_readable($projectPath)) {
            throw new RuntimeException(
                "Project path is not readable."
            );
        }
    }


    private function scanRepositories(string $projectPath): array
    {
        $repositories = [];

        $directories = scandir($projectPath);

        foreach ($directories as $directory) {

            if ($directory === '.' || $directory === '..') {
                continue;
            }

            $path = $projectPath . DIRECTORY_SEPARATOR . $directory;

            if (!is_dir($path)) {
                continue;
            }

            if ($this->isCpmApplication($path)) {
                continue;
            }

            if (!$this->isRepository($path)) {
                continue;
            }

            $repositories[] = [
                'folder' => $directory,
                'path' => realpath($path),
            ];
        }

        return $repositories;
    }


    private function isRepository(string $path): bool
    {
        return is_dir($path . DIRECTORY_SEPARATOR . '.git')
            && file_exists($path . DIRECTORY_SEPARATOR . 'composer.json');
    }

    private function isCpmApplication(
        string $path
    ): bool {

        return file_exists(
            $path
            . DIRECTORY_SEPARATOR
            . '.cpm-id'
        );
    }
}