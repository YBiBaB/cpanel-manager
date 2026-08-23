<?php

namespace Cpm\UI;

use Cpm\Registry\RegistryManager;
use Cpm\Project\ProjectManager;
use RuntimeException;

class ManageProjectMenu
{
    public function show(array $project): bool
    {
        while (true) {

            Console::line("");

            Console::title(
                "Manage Project"
            );

            Console::line("1. Rename");
            Console::line("2. Refresh Registration");
            Console::line("3. Remove from CPM");
            Console::line("4. Delete Project");
            Console::line("0. Back");

            Console::line("");

            $choice = Console::ask("Select");

            switch ($choice) {

                case "1":

                    Console::info(
                        "Coming soon."
                    );

                    break;

                case "2":

                    Console::info(
                        "Coming soon."
                    );

                    break;

                case "3":

                    if (
                        $this->removeFromCpm(
                            $project
                        )
                    ) {

                        return true;
                    }

                    break;

                case "4":

                    if (
                        $this->deleteProject(
                            $project
                        )
                    ) {

                        return true;
                    }

                    break;

                case "0":

                    return false;

                default:

                    Console::error(
                        "Invalid option."
                    );

                    return false;
            }
        }
    }

    private function removeFromCpm(
        array $project
    ): bool {

        Console::warning(
            "This project will no longer be managed by CPM."
        );

        Console::warning(
            "Your project files will NOT be deleted."
        );

        Console::line();


        if (
            !Console::confirm(
                "Remove CPM metadata (.cpm)?"
            )
        ) {

            return false;
        }


        $registry = new RegistryManager();
        $projectManager = new ProjectManager();


        if (
            !$registry->unregisterProject(
                $project['projectId']
            )
        ) {

            Console::error(
                "Failed to unregister project."
            );

            Console::pause();

            return false;
        }


        $projectManager->removeCpmDirectory(
            $project
        );


        Console::success(
            "Project removed from CPM."
        );

        Console::pause();

        return true;
    }

    private function deleteProject(
        array $project
    ): bool {

        Console::warning(
            "This will permanently delete the project."
        );

        Console::warning(
            "All files will be removed."
        );

        Console::line();


        if (
            !Console::confirm(
                "Continue?"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return false;
        }


        $confirm =
            Console::ask(
                "Type project name to confirm"
            );


        if (
            $confirm !==
            $project['projectName']
        ) {

            Console::error(
                "Project name does not match."
            );

            Console::pause();

            return false;
        }


        try {

            $manager =
                new ProjectManager();


            $manager->deleteProject(
                $project
            );


        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return false;
        }


        Console::success(
            "Project deleted."
        );

        Console::pause();

        return true;
    }
}