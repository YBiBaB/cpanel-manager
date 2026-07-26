<?php

namespace Cpm\UI;


class SettingsMenu
{
    public function show(): void
    {
        while(true){

            Console::title(
                "Settings"
            );


            Console::line(
                "1. Run System Check"
            );

            Console::line(
                "2. View CPM Information"
            );

            Console::line(
                "0. Back"
            );


            $choice = Console::ask(
                "Select"
            );


            switch($choice){

                case "1":

                    Console::info(
                        "System check selected."
                    );

                    break;


                case "2":

                    Console::info(
                        "Information selected."
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