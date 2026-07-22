<?php

require dirname(__DIR__) . "/vendor/autoload.php";


use Cpm\Registry\RegistryManager;


$registry = new RegistryManager();


$registry->registerProject(
    "123456",
    "test",
    realpath("../../")
);

print_r(
    $registry->findProjectById("123456")
);
