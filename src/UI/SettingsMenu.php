<?php

namespace Cpm\UI;

use Cpm\Registry\RegistryManager;
use Cpm\System\HomeDirectory;
use Cpm\System\SystemCheck;
use RuntimeException;

class SettingsMenu
{
    private const VERSION = 'v0.1.0';


    public function show(): void
    {
        while (true) {

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


            switch ($choice) {

                case "1":

                    $this->runSystemCheck();

                    break;


                case "2":

                    $this->showCpmInformation();

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

    private function runSystemCheck(): void
    {
        $systemCheck = new SystemCheck();

        if (!$systemCheck->check()) {

            Console::error(
                "System check failed."
            );

        }

        Console::pause();
    }

    private function showCpmInformation(): void
    {
        Console::title(
            "CPM Information"
        );

        Console::line(
            "Name       : CPanel Manager"
        );

        Console::line(
            "Version    : " . self::VERSION
        );

        Console::line(
            "PHP        : " . PHP_VERSION
        );


        try {

            $home = HomeDirectory::get();

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::line(
            "Home       : " . $home
        );


        $registry = new RegistryManager();

        $registryPath =
            $registry->getRegistryPath();


        Console::line(
            "Registry   : " . $registryPath
        );

        Console::line(
            "Exists     : "
            . (
                file_exists($registryPath)
                    ? "yes"
                    : "no"
            )
        );


        $projects =
            $registry->getProjects();

        Console::line(
            "Projects   : "
            . count($projects)
        );


        if (!empty($projects)) {

            Console::line();

            Console::info(
                "Registered projects:"
            );

            foreach ($projects as $project) {

                Console::line(
                    "- "
                    . $project['projectName']
                    . " ("
                    . $project['path']
                    . ")"
                );
            }
        }


        Console::pause();
    }
}
