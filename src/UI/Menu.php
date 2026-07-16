<?php

namespace Cpm\UI;

class Menu
{
    public function show(): void
    {
        echo PHP_EOL;

        echo "1. Add Project" . PHP_EOL;
        echo "2. Open Project" . PHP_EOL;
        echo "3. Settings" . PHP_EOL;
        echo "0. Exit" . PHP_EOL;

        echo PHP_EOL;
        echo "Select: ";
    }
}