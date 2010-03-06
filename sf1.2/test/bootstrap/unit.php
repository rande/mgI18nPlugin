<?php


$base_project = dirname(__FILE__).'/../../../../';


require_once $base_project.'/config/ProjectConfiguration.class.php';
$configuration = new ProjectConfiguration($base_project);
include($configuration->getSymfonyLibDir().'/vendor/lime/lime.php');
