<?php

namespace Cpm\Project;

use Cpm\UI\Console;

class RepositoryConfigurator
{
    private GitHelper $gitHelper;


    public function __construct()
    {
        $this->gitHelper = new GitHelper();
    }


    public function configure(array $repository): array
    {
        $folder = $repository['folder'];
        $path = $repository['path'];


        Console::title(
            "Configure {$folder}"
        );


        $defaultBranch =
            $this->gitHelper->getCurrentBranch($path);


        if ($defaultBranch === null) {
            $defaultBranch = '';
        }


        $name = Console::ask(
            "Name [{$folder}]",
            true
        );

        if ($name === '') {
            $name = $folder;
        }


        $branch = Console::ask(
            "Branch [{$defaultBranch}]",
            true
        );

        if ($branch === '') {
            $branch = $defaultBranch;
        }


        $domain = Console::ask(
            "Domain [{$folder}]",
            true
        );

        if ($domain === '') {
            $domain = $folder;
        }


        $documentRoot = Console::ask(
            "Document root [webroot]",
            true
        );

        if ($documentRoot === '') {
            $documentRoot = "webroot";
        }

        Console::success(
            "Repository '{$folder}' configured."
        );

        Console::line();

        return [
            'name' => $name,
            'folder' => $folder,
            'path' => $path,
            'branch' => $branch,
            'domain' => $domain,
            'documentRoot' => $documentRoot,
        ];
    }
}
