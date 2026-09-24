<?php

namespace Cpm\UI;

use Cpm\Config\ConfigManager;
use Cpm\Project\GitHelper;
use Cpm\Project\ProjectManager;
use Cpm\Project\RepositoryConfigurator;
use Cpm\Project\RepositorySelector;
use RuntimeException;

class BranchManagementMenu
{
    public function show(array $project): void
    {
        while (true) {

            Console::line("");

            Console::title(
                "Branch Management"
            );

            Console::line(
                "1. Add Branch"
            );

            Console::line(
                "2. Remove Branch Registration"
            );

            Console::line(
                "3. Delete Branch"
            );

            Console::line(
                "0. Back"
            );

            Console::line("");


            $choice = Console::ask(
                "Select"
            );


            switch ($choice) {

                case "1":

                    $this->addBranch(
                        $project
                    );

                    break;


                case "2":

                    $this->removeBranchRegistration(
                        $project
                    );

                    break;


                case "3":

                    $this->deleteBranch(
                        $project
                    );

                    break;


                case "0":

                    return;


                default:

                    Console::error(
                        "Invalid option."
                    );
            }
        }
    }

    private function addBranch(
        array $project
    ): void {

        Console::title(
            "Add Branch"
        );


        $config =
            $this->loadConfig(
                $project
            );


        if ($config === null) {

            return;
        }


        $reference =
            $this->resolveReferenceRepository(
                $config
            );


        if ($reference === null) {

            return;
        }


        $referencePath =
            $reference['path'] ?? '';


        if (
            $referencePath === ''
            || !is_dir($referencePath)
        ) {

            Console::error(
                "Reference repository path does not exist."
            );

            Console::pause();

            return;
        }


        $gitHelper = new GitHelper();


        try {

            Console::info(
                "Fetching remote branches..."
            );

            $gitHelper->fetch(
                $referencePath
            );

            $remoteUrl =
                $gitHelper->getRemoteUrl(
                    $referencePath
                );

            $remoteBranches =
                $gitHelper->listRemoteBranches(
                    $referencePath
                );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        $available =
            $this->findUnmanagedBranches(
                $remoteBranches,
                $config
            );


        if (empty($available)) {

            Console::info(
                "All remote branches are already managed."
            );

            Console::pause();

            return;
        }


        $branchName =
            $this->selectRemoteBranch(
                $available
            );


        if ($branchName === null) {

            return;
        }


        Console::line();


        $folder = Console::ask(
            "Folder [{$branchName}]",
            true
        );


        if ($folder === '') {
            $folder = $branchName;
        }


        if (
            $this->isFolderManaged(
                $folder,
                $config
            )
        ) {

            Console::error(
                "Folder is already managed: "
                . $folder
            );

            Console::pause();

            return;
        }


        Console::line();


        if (
            !Console::confirm(
                "Add branch '{$branchName}' as '{$folder}'"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return;
        }


        $manager = new ProjectManager();


        try {

            Console::info(
                "Preparing branch directory..."
            );

            $path =
                $manager->prepareBranchDirectory(
                    $project,
                    $branchName,
                    $folder,
                    $remoteUrl
                );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        $configurator =
            new RepositoryConfigurator();

        $repository =
            $configurator->configure(
                [
                    'folder' => $folder,
                    'path' => $path,
                ]
            );


        if (
            ($repository['branch'] ?? '')
            === ''
        ) {

            $repository['branch'] =
                $branchName;
        }


        try {

            $manager->appendRepository(
                $project,
                $repository
            );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::success(
            "Branch added successfully."
        );

        Console::pause();
    }

    private function removeBranchRegistration(
        array $project
    ): void {

        Console::title(
            "Remove Branch Registration"
        );

        Console::warning(
            "This branch will be removed from CPM configuration."
        );

        Console::warning(
            "Branch files will NOT be deleted."
        );

        Console::line();


        $selection =
            $this->selectRepository(
                $project
            );


        if ($selection === null) {

            return;
        }


        [
            'index' => $index,
            'repository' => $repository,
        ] = $selection;


        Console::line();


        if (
            !Console::confirm(
                "Remove '"
                . $repository['folder']
                . "' from configuration"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return;
        }


        try {

            $manager =
                new ProjectManager();

            $manager->removeBranchRegistration(
                $project,
                $index
            );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::success(
            "Branch removed from configuration."
        );

        Console::pause();
    }

    private function deleteBranch(
        array $project
    ): void {

        Console::title(
            "Delete Branch"
        );

        Console::warning(
            "This will permanently delete the branch directory."
        );

        Console::warning(
            "All files in this branch will be removed."
        );

        Console::line();


        $selection =
            $this->selectRepository(
                $project
            );


        if ($selection === null) {

            return;
        }


        [
            'index' => $index,
            'repository' => $repository,
        ] = $selection;


        Console::line();


        if (
            !Console::confirm(
                "Continue"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return;
        }


        $confirm =
            Console::ask(
                "Type branch folder name to confirm"
            );


        if (
            $confirm
            !==
            $repository['folder']
        ) {

            Console::error(
                "Branch folder name does not match."
            );

            Console::pause();

            return;
        }


        try {

            $manager =
                new ProjectManager();

            $manager->deleteBranch(
                $project,
                $index
            );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::success(
            "Branch deleted."
        );

        Console::pause();
    }

    private function loadConfig(
        array $project
    ): ?array {

        $configPath =
            $project['path']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


        try {

            return (new ConfigManager())
                ->load($configPath);

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return null;
        }
    }

    private function resolveReferenceRepository(
        array $config
    ): ?array {

        $repositories =
            $config['repositories'] ?? [];


        if (empty($repositories)) {

            Console::warning(
                "No managed branches found."
            );

            Console::info(
                "Add an existing branch first to discover the remote."
            );

            Console::pause();

            return null;
        }


        foreach ($repositories as $repository) {

            $path = $repository['path'] ?? '';


            if (
                $path !== ''
                && is_dir($path)
                && is_dir(
                    $path
                    . DIRECTORY_SEPARATOR
                    . '.git'
                )
            ) {

                return $repository;
            }
        }


        Console::error(
            "No valid managed branch found to read the remote from."
        );

        Console::pause();

        return null;
    }

    /**
     * @param string[] $remoteBranches
     * @return string[]
     */
    private function findUnmanagedBranches(
        array $remoteBranches,
        array $config
    ): array {

        $managed = [];


        foreach (
            $config['repositories'] ?? []
            as $repository
        ) {

            if (
                isset($repository['folder'])
                && $repository['folder'] !== ''
            ) {

                $managed[
                    $repository['folder']
                ] = true;
            }


            if (
                isset($repository['branch'])
                && $repository['branch'] !== ''
            ) {

                $managed[
                    $repository['branch']
                ] = true;
            }
        }


        $available = [];


        foreach ($remoteBranches as $branch) {

            if (isset($managed[$branch])) {
                continue;
            }


            $available[] = $branch;
        }


        return $available;
    }

    /**
     * @param string[] $branches
     */
    private function selectRemoteBranch(
        array $branches
    ): ?string {

        Console::line("");

        Console::info(
            "Unmanaged remote branches:"
        );


        foreach ($branches as $index => $branch) {

            Console::line(
                ($index + 1)
                . ". "
                . $branch
            );
        }


        Console::line(
            "0. Back"
        );


        $choice = Console::ask(
            "Select branch"
        );


        if ($choice === "0") {
            return null;
        }


        $index = intval($choice) - 1;


        if (!isset($branches[$index])) {

            Console::error(
                "Invalid branch."
            );

            return null;
        }


        return $branches[$index];
    }

    private function isFolderManaged(
        string $folder,
        array $config
    ): bool {

        foreach (
            $config['repositories'] ?? []
            as $repository
        ) {

            if (
                ($repository['folder'] ?? null)
                === $folder
            ) {

                return true;
            }
        }


        return false;
    }

    private function selectRepository(
        array $project
    ): ?array {

        $config =
            $this->loadConfig(
                $project
            );


        if ($config === null) {

            return null;
        }


        $selector =
            new RepositorySelector();

        $index =
            $selector->selectIndex(
                $config
            );


        if ($index === null) {

            return null;
        }


        return [

            'index' => $index,

            'repository' =>
                $config['repositories'][$index],

        ];
    }
}
