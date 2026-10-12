<?php

namespace Cpm\Project;

use Cpm\UI\Console;
use Cpm\Utils\PathHelper;

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
            "Document root [webroot] (relative, absolute, or ~/...)",
            true
        );

        if ($documentRoot === '') {
            $documentRoot = "webroot";
        } else {
            $expanded = PathHelper::expandHome($documentRoot);

            if ($expanded !== null) {
                $documentRoot = $expanded;
            }

            // Resolve only when an absolute path was given / expanded.
            if (str_starts_with($documentRoot, DIRECTORY_SEPARATOR)) {
                $resolved = realpath($documentRoot);

                if ($resolved !== false) {
                    $documentRoot = $resolved;
                }
            }
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
