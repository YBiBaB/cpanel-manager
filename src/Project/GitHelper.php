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
                $this->formatFailure(
                    "Git fetch failed.",
                    $result['output']
                )
            );
        }
    }


    public function pull(
        string $path,
        ?string $branch = null,
        string $remote = 'origin'
    ): void {

        if (
            $branch !== null
            && $branch !== ''
        ) {

            $command =
                "git pull "
                . escapeshellarg($remote)
                . " "
                . escapeshellarg($branch);

        } else {

            $command = "git pull";
        }


        $result = $this->runIn(
            $path,
            $command
        );


        if (!$result['success']) {

            throw new RuntimeException(
                $this->formatFailure(
                    "Git pull failed.",
                    $result['output']
                )
            );
        }
    }


    /**
     * @return string[]
     */
    public function status(
        string $path
    ): array {

        $result = $this->runIn(
            $path,
            "git status"
        );


        if (!$result['success']) {

            throw new RuntimeException(
                $this->formatFailure(
                    "Git status failed.",
                    $result['output']
                )
            );
        }


        return $result['output'];
    }


    public function hasCommits(
        string $path
    ): bool {

        $result = $this->runIn(
            $path,
            "git rev-parse --verify HEAD"
        );


        return $result['success'];
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


    /**
     * @param string[] $output
     */
    private function formatFailure(
        string $message,
        array $output
    ): string {

        $detail = trim(
            implode(
                PHP_EOL,
                $output
            )
        );


        if ($detail === '') {
            return $message;
        }


        return $message
            . PHP_EOL
            . $detail;
    }
}
