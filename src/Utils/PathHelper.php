<?php

namespace Cpm\Utils;

class PathHelper
{
    /**
     * Expand a leading "~" or "~/" using HOME, then resolve with realpath().
     */
    public static function resolve(string $path): string|false
    {
        $expanded = self::expandHome($path);

        if ($expanded === null) {
            return false;
        }

        return realpath($expanded);
    }


    /**
     * Expand "~" / "~/" only. Returns null when the path is empty
     * or HOME cannot be determined for a home-relative path.
     */
    public static function expandHome(string $path): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if ($path === '~' || str_starts_with($path, '~/')) {
            $home = getenv('HOME');

            if ($home === false || $home === '') {
                return null;
            }

            if ($path === '~') {
                return $home;
            }

            return $home
                . DIRECTORY_SEPARATOR
                . substr($path, 2);
        }

        return $path;
    }
}
