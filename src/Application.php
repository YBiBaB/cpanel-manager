<?php

namespace Cpm;

use Cpm\Environment\EnvironmentChecker;
use Cpm\UI\Menu;

class Application
{
    public function run(): void
    {
        $this->showBanner();

        $checker = new EnvironmentChecker();
        $checker->check();

        $menu = new Menu();
        $menu->show();
    }

    private function showBanner(): void
    {
        echo PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo "        CPanel Manager v0.1.0" . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo PHP_EOL;
    }
}