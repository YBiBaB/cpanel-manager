<?php

namespace Cpm;

use Cpm\System\SystemCheck;
use Cpm\UI\MainMenu;
use Cpm\UI\Console;

class Application
{
    public function run(): void
    {
        $this->showBanner();

        $systemCheck = new SystemCheck();

        if (!$systemCheck->check()) {
            Console::error(
                "System check failed."
            );

            return;
        }

        $menu = new MainMenu();

        $menu->show();
    }

    private function showBanner(): void
    {
        Console::banner(
            "CPanel Manager",
            "v0.1.0"
        );
    }
}