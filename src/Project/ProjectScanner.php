<?php

namespace Cpm\Project;

use RuntimeException;

class ProjectScanner
{
    public function scan(string $projectPath): array
    {
        $resolvedPath = $this->resolvePath($projectPath);

        $this->validateProjectPath(
            $resolvedPath,
            $projectPath
        );

        return [
            'projectName' => basename($resolvedPath),
            'projectPath' => $resolvedPath,
            'repositories' => $this->scanRepositories($resolvedPath),
        ];
    }


    private function resolvePath(string $path): string|false
    {
        $path = trim($path);

        if ($path === '') {
            return false;
        }

        // Shell-style ~ is not expanded by PHP realpath().
        if ($path === '~') {
            $home = getenv('HOME');

            if ($home === false || $home === '') {
                return false;
            }

            $path = $home;
        } elseif (str_starts_with($path, '~/')) {
            $home = getenv('HOME');

            if ($home === false || $home === '') {
                return false;
            }

            $path =
                $home
                . DIRECTORY_SEPARATOR
                . substr($path, 2);
        }

        return realpath($path);
    }


    private function validateProjectPath(
        string|false $projectPath,
        string $originalPath
    ): void {
        if ($projectPath === false) {
            $cwd = getcwd() ?: '(unknown)';

            throw new RuntimeException(
                "Project path does not exist: {$originalPath}\n"
                . "Current directory: {$cwd}\n"
                . "Tip: use an absolute path, e.g. /home/USER/FIT3048 or ~/FIT3048"
            );
        }

        if (!is_dir($projectPath)) {
            throw new RuntimeException(
                "Project path is not a directory: {$projectPath}"
            );
        }

        if (!is_readable($projectPath)) {
            throw new RuntimeException(
                "Project path is not readable: {$projectPath}"
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