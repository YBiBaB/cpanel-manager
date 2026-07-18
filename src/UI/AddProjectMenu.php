<?php

namespace Cpm\UI;

use Cpm\Project\ProjectManager;

class AddProjectMenu
{
    private ProjectManager $projectManager;


    public function __construct()
    {
        $this->projectManager = new ProjectManager();
    }


    public function show(): void
    {
        while (true) {

            Console::title(
                "Add Project"
            );


            Console::line(
                "1. Add Existing Project"
            );

            Console::line(
                "2. Add New Project"
            );

            Console::line(
                "0. Back"
            );


            Console::line();


            $choice = Console::ask(
                "Select",
                false,
                function ($input) {

                    return in_array(
                        $input,
                        [
                            '1',
                            '2',
                            '0'
                        ]
                    );

                },
                "Invalid option."
            );


            switch ($choice) {

                case '1':

                    $this->projectManager
                        ->addExistingProject();

                    break;


                case '2':

                    Console::info(
                        "Add New Project is not implemented yet."
                    );

                    break;


                case '0':

                    return;
            }
        }
    }
}