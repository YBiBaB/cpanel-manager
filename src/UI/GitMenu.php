<?php

namespace Cpm\UI;

use Cpm\Project\GitHelper;
use RuntimeException;

class GitMenu
{
    public function show(
        array $project,
        array $repository
    ): void {

        while (true) {

            $path =
                $repository['path'] ?? '';


            if (
                $path === ''
                || !is_dir($path)
            ) {

                Console::error(
                    "Repository path does not exist."
                );

                Console::pause();

                return;
            }


            $gitHelper = new GitHelper();

            $configuredBranch =
                $repository['branch'] ?? '';

            $currentBranch =
                $gitHelper->getCurrentBranch(
                    $path
                );


            Console::line("");

            Console::title(
                "Git: "
                . ($repository['folder'] ?? '')
            );

            Console::line(
                "Project           : "
                . ($project['projectName'] ?? '')
            );

            Console::line(
                "Configured branch : "
                . (
                    $configuredBranch !== ''
                        ? $configuredBranch
                        : '(none)'
                )
            );

            Console::line(
                "Current branch    : "
                . (
                    $currentBranch !== null
                        ? $currentBranch
                        : '(unknown)'
                )
            );

            Console::line(
                "Path              : "
                . $path
            );


            if (
                $configuredBranch !== ''
                && $currentBranch !== null
                && $configuredBranch !== $currentBranch
            ) {

                Console::line();

                Console::warning(
                    "Current branch does not match configured branch."
                );
            }


            Console::line("");

            Console::line("1. Status");
            Console::line("2. Fetch");
            Console::line("3. Pull");
            Console::line("0. Back");

            Console::line("");


            $choice = Console::ask(
                "Select"
            );


            switch ($choice) {

                case "1":

                    $this->status(
                        $path
                    );

                    break;


                case "2":

                    $this->fetch(
                        $path
                    );

                    break;


                case "3":

                    $this->pull(
                        $path,
                        $configuredBranch
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

    private function status(
        string $path
    ): void {

        Console::title(
            "Git Status"
        );


        try {

            $lines =
                (new GitHelper())
                    ->status($path);

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::line();


        if (empty($lines)) {

            Console::info(
                "No status output."
            );

        } else {

            foreach ($lines as $line) {

                Console::line(
                    $line
                );
            }
        }


        Console::pause();
    }

    private function fetch(
        string $path
    ): void {

        Console::title(
            "Git Fetch"
        );


        try {

            Console::info(
                "Fetching..."
            );

            (new GitHelper())->fetch(
                $path
            );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::success(
            "Fetch completed."
        );

        Console::pause();
    }

    private function pull(
        string $path,
        string $configuredBranch
    ): void {

        Console::title(
            "Git Pull"
        );


        try {

            Console::info(
                "Pulling..."
            );

            (new GitHelper())->pull(
                $path,
                $configuredBranch !== ''
                    ? $configuredBranch
                    : null
            );

        } catch (RuntimeException $e) {

            Console::error(
                $e->getMessage()
            );

            Console::pause();

            return;
        }


        Console::success(
            "Pull completed."
        );

        Console::pause();
    }
}
