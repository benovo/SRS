<?php

use \Nette\Application\Routers\Route;
use \Nette\Diagnostics\Debugger;


SetLocale(LC_ALL, "Czech");

// Load Nette Framework or autoloader generated by Composer
require LIBS_DIR . '/autoload.php';

Debugger::timer();
// Configure application
$configurator = new \Nette\Config\Configurator;


\Nella\Console\Config\Extension::register($configurator);
\Nella\Doctrine\Config\Extension::register($configurator);
\Nella\Doctrine\Config\MigrationsExtension::register($configurator);
\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('JMS\Serializer\Annotation', LIBS_DIR . "/jms/serializer/src");

// Enable RobotLoader - this will load all classes automatically

$temp_dir = __DIR__ . '/../temp';
$configurator->setTempDirectory($temp_dir);
$configurator->createRobotLoader()
    ->addDirectory(APP_DIR)
    ->addDirectory(LOCAL_LIBS_DIR)
    ->addDirectory(TESTS_DIR)
    ->register();


$config = \Nette\Utils\Neon::decode(file_get_contents(__DIR__ . '/config/config.neon'));
$isDebug = $config['common']['parameters']['debug'];

if ($isDebug) {
    $configurator->setDebugMode();
    $configurator->enableDebugger(__DIR__ . '/../log');
} else {
    \Nette\Diagnostics\Debugger::$logDirectory = __DIR__ . '/../log';
    \Nette\Diagnostics\Debugger::$email = $config['common']['parameters']['contact_email'];
    $configurator->setDebugMode($configurator::NONE);
}

$environment = $isDebug == true ? 'development' : 'production';
$configurator->addConfig(__DIR__ . '/config/config.neon', $environment);


$container = $configurator->createContainer();


// Setup router
$container->router[] = new Route('index.php', 'Front:Homepage:default', Route::ONE_WAY);
//$container->router[] = new Route('admin/', 'Back:Dashboard:default');

$container->router[] = new Route('api/program/<action>[/<id>][/<area>]', array(
    'module' => 'Back:Program',
    'presenter' => 'Api',
    'action' => 'default',
    'id' => null,
    'area' => null
));

$container->router[] = new Route('admin/cms/<presenter>/<action>[/<id>][/<area>]', array(
    'module' => 'Back:CMS',
    'presenter' => 'Page',
    'action' => 'default',
    'id' => null,
    'area' => null
));

$container->router[] = new Route('admin/program/<presenter>/<action>[/<id>][/<area>]', array(
    'module' => 'Back:Program',
    'presenter' => 'Block',
    'action' => 'list',
    'id' => null,
    'area' => null
));

$container->router[] = new Route('admin/<presenter>/<action>[/<id>][/<area>]', array(
    'module' => 'Back',
    'presenter' => 'Dashboard',
    'action' => 'default',
    'id' => null,
    'area' => null
));


$container->router[] = new Route('install/<presenter>/<action>/<id>/', array(
    'module' => 'Install',
    'presenter' => 'Install',
    'action' => 'default',
    'id' => null
));
$container->router[] = new Route('login/', 'Auth:login');
$container->router[] = new Route('logout/', 'Auth:logout');

$pageRepo = $container->database->getRepository('\SRS\Model\CMS\Page');
$container->router[] = new Route('[!<pageId [a-z-0-9]+>]', array(
    'module' => 'Front',
    'presenter' => 'Page',
    'action' => 'default',
    'pageId' => array(
        Route::FILTER_IN => callback($pageRepo, 'slugToId'),
        Route::FILTER_OUT => callback($pageRepo, "idToSlug")
    )
));

$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Front:Homepage:default');


if (!defined('CANCEL_START_APP')) {
    $container->application->run();
}
