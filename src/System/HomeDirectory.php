<?php

namespace Cpm\System;

use RuntimeException;

class HomeDirectory
{
    public static function get(): string
    {
        $home = getenv('HOME');

        if ($home !== false && is_dir($home)) {
            return $home;
        }


        throw new RuntimeException(
            "Cannot determine user home directory."
        );
    }
}