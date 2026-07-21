<?php

namespace Cpm\UI;


class ProjectActionMenu
{
    public function show(
        array $project
    ): void {

        while (true) {

            Console::line("");

            Console::title(
                "Project: "
                . $project['projectName']
            );


            Console::line(
                "1. Open"
            );

            Console::line(
                "2. Manage"
            );

            Console::line(
                "0. Back"
            );


            $choice = Console::ask(
                "Select"
            );


            switch ($choice) {


                case "1":

                    Console::info(
                        "Open selected."
                    );

                    break;


                case "2":

                    Console::info(
                        "Manage selected."
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
}