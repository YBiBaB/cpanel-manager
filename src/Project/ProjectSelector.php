<?php

namespace Cpm\Project;

use Cpm\UI\Console;
use Cpm\Registry\RegistryManager;


class ProjectSelector
{
    public function __construct(
        private RegistryManager $registry
    ) {
    }


    public function select(): ?array
    {
        $projects =
            $this->registry->getProjects();


        if (empty($projects)) {

            Console::warning(
                "No projects found."
            );

            Console::info(
                "Use 'Add Project' to register a project."
            );

            Console::pause();

            return null;
        }


        Console::line("");

        foreach ($projects as $index => $project) {

            Console::line(
                ($index + 1)
                . ". "
                . $project['projectName']
            );
        }


        Console::line(
            "0. Back"
        );


        $choice = Console::ask(
            "Select project"
        );


        if ($choice === "0") {
            return null;
        }


        $index = intval($choice) - 1;


        if (!isset($projects[$index])) {

            Console::error(
                "Invalid project."
            );

            return null;
        }


        return $projects[$index];
    }
}