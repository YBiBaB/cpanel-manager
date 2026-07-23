<?php

namespace Cpm\UI;

class ManageProjectMenu
{
    public function show(array $project): void
    {
        while (true) {

            Console::line("");

            Console::title(
                "Manage Project"
            );

            Console::line("1. Rename");
            Console::line("2. Refresh Registration");
            Console::line("3. Remove from CPM");
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

                    Console::info(
                        "Coming soon."
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