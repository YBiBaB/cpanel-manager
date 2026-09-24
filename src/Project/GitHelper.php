<?php

namespace Cpm\Project;

use Cpm\System\CommandRunner;
use RuntimeException;

class GitHelper
{
    public function getCurrentBranch(
        string $path
    ): ?string {

        $result = $this->runIn(
            $path,
            "git branch --show-current"
        );


        if (
            !$result['success']
            || empty($result['output'])
        ) {
            return null;
        }


        return trim(
            $result['output'][0]
        );
    }


    public function getRemoteUrl(
        string $path,
        string $remote = 'origin'
    ): string {

        $result = $this->runIn(
            $path,
            "git remote get-url "
            . escapeshellarg($remote)
        );


        if (
            !$result['success']
            || empty($result['output'])
        ) {

            throw new RuntimeException(
                "Cannot read git remote '{$remote}'."
            );
        }


        return trim(
            $result['output'][0]
        );
    }


    public function fetch(
        string $path,
        string $remote = 'origin'
    ): void {

        $result = $this->runIn(
            $path,
            "git fetch "
            . escapeshellarg($remote)
            . " --prune"
        );


        if (!$result['success']) {

            throw new RuntimeException(
                "Git fetch failed."
            );
        }
    }


    public function pull(
        string $path
    ): void {

        $result = $this->runIn(
            $path,
            "git pull"
        );


        if (!$result['success']) {

            throw new RuntimeException(
                "Git pull failed."
            );
        }
    }


    /**
     * @return string[] branch names without remote prefix
     */
    public function listRemoteBranches(
        string $path,
        string $remote = 'origin'
    ): array {

        $result = $this->runIn(
            $path,
            "git branch -r"
        );


        if (!$result['success']) {

            throw new RuntimeException(
                "Cannot list remote branches."
            );
        }


        $branches = [];

        $prefix = $remote . '/';


        foreach ($result['output'] as $line) {

            $line = trim($line);


            if ($line === '') {
                continue;
            }


            if (str_contains($line, '->')) {
                continue;
            }


            if (!str_starts_with($line, $prefix)) {
                continue;
            }


            $branch = substr(
                $line,
                strlen($prefix)
            );


            if ($branch === '') {
                continue;
            }


            $branches[] = $branch;
        }


        $branches = array_values(
            array_unique($branches)
        );

        sort($branches);


        return $branches;
    }


    public function cloneBranch(
        string $remoteUrl,
        string $branch,
        string $targetPath
    ): void {

        if (is_dir($targetPath)) {

            throw new RuntimeException(
                "Target directory already exists."
            );
        }


        $parent = dirname($targetPath);


        if (!is_dir($parent)) {

            throw new RuntimeException(
                "Parent directory does not exist."
            );
        }


        $commandRunner = new CommandRunner();

        $commandRunner->setWorkingDirectory(
            $parent
        );


        $result = $commandRunner->run(
            "git clone"
            . " --branch "
            . escapeshellarg($branch)
            . " --single-branch "
            . escapeshellarg($remoteUrl)
            . " "
            . escapeshellarg(
                basename($targetPath)
            )
        );


        if (!$result['success']) {

            throw new RuntimeException(
                "Git clone failed."
            );
        }
    }


    private function runIn(
        string $path,
        string $command
    ): array {

        $commandRunner = new CommandRunner();

        $commandRunner->setWorkingDirectory(
            $path
        );


        return $commandRunner->run(
            $command
        );
    }
}
