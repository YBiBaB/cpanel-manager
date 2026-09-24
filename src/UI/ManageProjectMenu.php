<?php

namespace Cpm\UI;

use Cpm\Registry\RegistryManager;
use Cpm\Project\ProjectManager;
use Cpm\Project\ProjectScanner;
use RuntimeException;

class ManageProjectMenu
{
    public function show(array &$project): bool
    {
        while (true) {

            Console::line("");

            Console::title(
                "Manage Project"
            );

            Console::line("1. Rename");
            Console::line("2. Refresh Registration");
            Console::line("3. Branch Management");
            Console::line("4. Repair Path");
            Console::line("5. Remove from CPM");
            Console::line("6. Delete Project");
            Console::line("0. Back");

            Console::line("");

            $choice = Console::ask("Select");

            switch ($choice) {

                case "1":

                    $this->renameProject(
                        $project
                    );

                    break;

                case "2":

                    $this->refreshRegistration(
                        $project
                    );

                    break;

                case "3":

                    $menu =
                        new BranchManagementMenu();

                    $menu->show(
                        $project
                    );

                    break;

                case "4":

                    $this->repairPath(
                        $project
                    );

                    break;

                case "5":

                    if (
                        $this->removeFromCpm(
                            $project
                        )
                    ) {

                        return true;
                    }

                    break;

                case "6":

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

    private function refreshRegistration(
        array $project
    ): void {

        Console::title(
            "Refresh Registration"
        );


        try {

            $scanner = new ProjectScanner();

            $scanned =
                $scanner->scan(
                    $project['path']
                );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        $manager = new ProjectManager();

        $manager->refreshRegistration(
            $scanned,
            $project['projectId'],
            $project['projectName']
        );
    }

    private function renameProject(
        array &$project
    ): void {

        Console::title(
            "Rename Project"
        );

        Console::line(
            "Current name: "
            . $project['projectName']
        );

        Console::line();


        $newName = Console::ask(
            "New name"
        );


        if (
            $newName
            ===
            $project['projectName']
        ) {

            Console::info(
                "Name unchanged."
            );

            Console::pause();

            return;
        }


        Console::line();


        if (
            !Console::confirm(
                "Rename '"
                . $project['projectName']
                . "' to '"
                . $newName
                . "'"
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

            $manager->renameProject(
                $project,
                $newName
            );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        $project['projectName'] = $newName;


        Console::success(
            "Project renamed successfully."
        );

        Console::info(
            "Project directory was not changed."
        );

        Console::pause();
    }

    private function repairPath(
        array &$project
    ): void {

        Console::title(
            "Repair Path"
        );

        Console::line(
            "Registered path: "
            . ($project['path'] ?? '(none)')
        );

        Console::line();


        $manager = new ProjectManager();

        $status =
            $manager->validateRegistration(
                $project
            );


        if ($status['valid']) {

            Console::success(
                "Current registration looks valid."
            );

            Console::info(
                "No path repair needed."
            );

            Console::pause();

            return;
        }


        Console::warning(
            "Current registration is broken."
        );

        Console::error(
            $status['reason']
        );


        Console::line();


        $newPath = Console::ask(
            "New project path"
        );


        Console::line();


        if (
            !Console::confirm(
                "Update project path to '"
                . $newPath
                . "'"
            )
        ) {

            Console::info(
                "Operation cancelled."
            );

            return;
        }


        try {

            $updated =
                $manager->repairPath(
                    $project,
                    $newPath
                );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        $project['path'] =
            $updated['path'];

        $project['projectName'] =
            $updated['projectName'];


        Console::success(
            "Project path repaired successfully."
        );

        Console::line(
            "Path: "
            . $updated['path']
        );

        Console::pause();
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