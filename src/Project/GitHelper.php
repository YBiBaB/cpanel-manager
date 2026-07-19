<?php

namespace Cpm\Project;

use Cpm\System\CommandRunner;

class GitHelper
{
    public function getCurrentBranch(
        string $path
    ): ?string {

        $commandRunner = new CommandRunner();

        $commandRunner->setWorkingDirectory(
            $path
        );

        $result = $commandRunner->run(
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
}