<?php

namespace Cpm\Registry;

use Cpm\System\HomeDirectory;
use RuntimeException;

class RegistryManager
{
    private string $registryPath;


    public function __construct()
    {
        $this->registryPath =
            HomeDirectory::get()
            . DIRECTORY_SEPARATOR
            . ".cpm"
            . DIRECTORY_SEPARATOR
            . "registry.json";
    }


    public function getProjects(): array
    {
        $registry = $this->load();

        return $registry['projects'];
    }


    public function addProject(
        string $projectId,
        string $projectName,
        string $path
    ): void {

        $registry = $this->load();


        foreach ($registry['projects'] as $project) {

            if ($project['projectId'] === $projectId) {

                return;
            }
        }


        $registry['projects'][] = [

            'projectId' => $projectId,

            'projectName' => $projectName,

            'path' => $path,

        ];


        $this->save($registry);
    }



    public function findProjectById(
        string $projectId
    ): ?array {

        $registry = $this->load();


        foreach ($registry['projects'] as $project) {

            if ($project['projectId'] === $projectId) {

                return $project;
            }
        }


        return null;
    }



    public function updateProjectPath(
        string $projectId,
        string $path
    ): bool {

        $registry = $this->load();


        foreach ($registry['projects'] as &$project) {

            if ($project['projectId'] === $projectId) {

                $project['path'] = $path;

                $this->save($registry);

                return true;
            }
        }


        return false;
    }



    private function load(): array
    {
        if (!file_exists($this->registryPath)) {

            return [
                'registryVersion' => 1,
                'projects' => []
            ];
        }


        $content =
            file_get_contents(
                $this->registryPath
            );


        if ($content === false) {

            throw new RuntimeException(
                "Cannot read registry."
            );
        }


        $registry =
            json_decode(
                $content,
                true
            );


        if (!is_array($registry)) {

            throw new RuntimeException(
                "Invalid registry."
            );
        }


        return $registry;
    }



    private function save(array $registry): void
    {
        $directory =
            dirname(
                $this->registryPath
            );


        if (!is_dir($directory)) {

            mkdir(
                $directory,
                0755,
                true
            );
        }


        file_put_contents(
            $this->registryPath,
            json_encode(
                $registry,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            )
        );
    }
}