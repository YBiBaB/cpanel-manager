<?php

namespace Cpm\UI;

use Cpm\Project\ProjectManager;

class Menu
{
    private ProjectManager $projectManager;

    public function __construct()
    {
        $this->projectManager = new ProjectManager();
    }
    public function show(): void
    {
        while (true) {

            Console::line("");

            Console::line("1. Add Project");
            Console::line("2. Open Project");
            Console::line("3. Settings");
            Console::line("0. Exit");

            Console::line("");


            $choice = Console::ask(
                "Select"
            );


            switch ($choice) {

                case "1":
                    $this->projectManager
                        ->addExistingProject();
                    break;


                case "2":
                    Console::info(
                        "Open Project selected."
                    );
                    break;


                case "3":
                    Console::info(
                        "Settings selected."
                    );
                    break;


                case "0":
                    Console::info(
                        "Goodbye."
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