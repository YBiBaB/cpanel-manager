<?php

namespace Cpm\UI;

class ProjectActionMenu
{
    public function show(array &$project): void
    {
        while (true) {

            Console::line("");

            Console::title(
                $project['projectName']
            );

            Console::line("1. Open");
            Console::line("2. Manage");
            Console::line("0. Back");

            Console::line("");

            $choice = Console::ask("Select");

            switch ($choice) {

                case "1":

                    $menu =
                        new OpenProjectMenu();

                    $menu->show($project);

                    break;


                case "2":

                    $menu =
                        new ManageProjectMenu();


                    $changed =
                        $menu->show(
                            $project
                        );


                    if ($changed) {

                        return;
                    }

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
}