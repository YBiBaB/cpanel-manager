<?php

namespace Cpm\Project;

class GitHelper
{
    public function getCurrentBranch(string $path): ?string
    {
        $currentPath = getcwd();

        chdir($path);

        exec(
            "git branch --show-current",
            $output,
            $code
        );

        chdir($currentPath);


        if ($code !== 0 || empty($output)) {
            return null;
        }

        return trim($output[0]);
    }
}