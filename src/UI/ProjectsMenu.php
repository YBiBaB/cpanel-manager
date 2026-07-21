<?php

namespace Cpm\UI;

use Cpm\Project\ProjectSelector;
use Cpm\Registry\RegistryManager;

class ProjectsMenu
{
    public function show(): void
    {
        while (true) {

            Console::line("");

            Console::title(
                "Projects"
            );


            $selector = new ProjectSelector(
                new RegistryManager()
            );


            $project = $selector->select();


            if ($project === null) {

                return;
            }


            $projectMenu =
                new ProjectActionMenu();


            $projectMenu->show(
                $project
            );
        }
    }
}