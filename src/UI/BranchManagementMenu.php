<?php

namespace Cpm\UI;

use Cpm\Config\ConfigManager;
use Cpm\Project\ProjectManager;
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
                "1. Remove Branch Registration"
            );

            Console::line(
                "2. Delete Branch"
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

                    $this->removeBranchRegistration(
                        $project
                    );

                    break;


                case "2":

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

    private function selectRepository(
        array $project
    ): ?array {

        $configPath =
            $project['path']
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "config.json";


        try {

            $config =
                (new ConfigManager())
                    ->load($configPath);

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

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
