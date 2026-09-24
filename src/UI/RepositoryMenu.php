<?php

namespace Cpm\UI;

class RepositoryMenu
{
    public function show(
        array $project,
        array $repository
    ): void {

        while (true) {

            Console::line("");

            Console::title(
                "Repository: "
                . $repository['folder']
            );

            Console::line(
                "Project : "
                . $project['projectName']
            );

            Console::line(
                "Branch  : "
                . $repository['branch']
            );

            Console::line(
                "Path    : "
                . $repository['path']
            );

            Console::line("");

            Console::line("1. Git");
            Console::line("2. Information");
            Console::line("0. Back");

            Console::line("");

            $choice = Console::ask("Select");

            switch ($choice) {

                case "1":

                    $menu =
                        new GitMenu();

                    $menu->show(
                        $project,
                        $repository
                    );

                    break;


                case "2":

                    Console::line("");

                    Console::line(
                        "Repository information:"
                    );

                    Console::line(
                        "Folder : "
                        . $repository['folder']
                    );

                    Console::line(
                        "Branch : "
                        . $repository['branch']
                    );

                    Console::line(
                        "Path   : "
                        . $repository['path']
                    );

                    Console::pause();

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