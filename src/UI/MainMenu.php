<?php

namespace Cpm\UI;

class MainMenu
{
    public function show(): void
    {
        while (true) {

            Console::line("");

            Console::line("1. Add Project");
            Console::line("2. Projects");
            Console::line("3. Settings");
            Console::line("0. Exit");

            Console::line("");


            $choice = Console::ask(
                "Select"
            );


            switch ($choice) {

                case "1":

                    $addProjectMenu =
                        new AddProjectMenu();

                    $addProjectMenu->show();

                    break;


                case "2":

                    $projectsMenu =
                        new ProjectsMenu();

                    $projectsMenu->show();

                    break;


                case "3":

                    $settingsMenu =
                        new SettingsMenu();

                    $settingsMenu->show();

                    break;


                case "0":

                    Console::info(
                        "Bye."
                    );

                    return;


                default:

                    Console::error(
                        "Invalid option."
                    );
            }
        }
    }
}