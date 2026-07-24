<?php

namespace Cpm\Project;

use Cpm\UI\Console;

class RepositorySelector
{
    public function select(array $config): ?array
    {
        $repositories = $config['repositories'] ?? [];

        if (empty($repositories)) {

            Console::warning(
                "No repositories found."
            );

            return null;
        }

        Console::line("");

        foreach ($repositories as $index => $repository) {

            Console::line(
                ($index + 1)
                . ". "
                . $repository['folder']
                . " ("
                . $repository['branch']
                . ")"
            );
        }

        Console::line("0. Back");

        $choice = Console::ask(
            "Select repository"
        );

        if ($choice === "0") {
            return null;
        }

        $index = intval($choice) - 1;

        if (!isset($repositories[$index])) {

            Console::error(
                "Invalid repository."
            );

            return null;
        }

        return $repositories[$index];
    }
}