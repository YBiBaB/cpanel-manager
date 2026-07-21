<?php

require dirname(__DIR__) . "/vendor/autoload.php";


use Cpm\Registry\RegistryManager;


$registry = new RegistryManager();


$registry->addProject(
    "123456",
    "test",
    realpath("../../")
);

print_r(
    $registry->findProjectById("123456")
);

$registry->updateProjectPath(
    "123456",
    realpath("../../FIT3047")
);


print_r(
    $registry->getProjects()
);

print_r(
    $registry->findProjectById("123456")
);

