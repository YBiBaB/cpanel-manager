<?php

namespace Cpm\Filesystem;
class Directory
{
    public function delete(
        string $directory
    ): void
    {

        $items = array_diff(
            scandir($directory),
            ['.', '..']
        );


        foreach ($items as $item) {

            $path =
                $directory
                . DIRECTORY_SEPARATOR
                . $item;


            if (is_dir($path)) {

                $this->delete(
                    $path
                );

                continue;
            }


            unlink($path);
        }


        rmdir($directory);
    }
}