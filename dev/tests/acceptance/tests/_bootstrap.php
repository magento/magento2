<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$bootstrapRoot = dirname(__DIR__);
$projectRootPath = $bootstrapRoot;

// If we're under a vendor directory, find project root.
if (strpos($bootstrapRoot, '/vendor') !== false) {
    $projectRootPath = substr($bootstrapRoot, 0, strpos($bootstrapRoot, '/vendor/'));
}

define('PROJECT_ROOT', $projectRootPath);


//Load base paths from composer autoload file
$autoloadFile = PROJECT_ROOT . '/vendor/autoload.php';

$loader = require $autoloadFile;

// Package Names.
$FW_PACKAGE_NAME = "Magento\FunctionalTestingFramework\\";
$TESTS_PACKAGE_NAME = "TODO";
$MAGENTO_PACKAGE_NAME = "Magento\\";

// Find framework path
$COMPOSER_FW_FULL_PREFIX = $loader->getPrefixesPsr4()[$FW_PACKAGE_NAME][0] ?? null;
if ($COMPOSER_FW_FULL_PREFIX === null) {
    throw new Exception(
        "You must have the magento/magento2-functional-testing-framework 
        installed to be able to generate tests."
    );
}
$FW_PATH = substr(
    $COMPOSER_FW_FULL_PREFIX,
    0,
    strpos($COMPOSER_FW_FULL_PREFIX, "/src/Magento/FunctionalTestingFramework")
);

// Find tests path
$COMPOSER_TEST_FULL_PREFIX = $loader->getPrefixesPsr4()[$TESTS_PACKAGE_NAME][0] ?? null;
if ($COMPOSER_TEST_FULL_PREFIX === null) {
    $TEST_PATH = __DIR__ . "/functional";
} else {
    // Can't determine what to trim; we don't know the package name/structure yet
    $TEST_PATH = $COMPOSER_TEST_FULL_PREFIX;
}

// We register "Magento\\" to "tests/functional/Magento" for our own class loading, need to try and find a
// prefix that isn't that one.
$COMPOSER_MAGENTO_PREFIXES = $loader->getPrefixesPsr4()[$MAGENTO_PACKAGE_NAME];
$COMPOSER_MAGENTO_FULL_PREFIX = null;
foreach ($COMPOSER_MAGENTO_PREFIXES as $path) {
    if (strpos($path, "tests/functional/Magento") === 0) {
        $COMPOSER_MAGENTO_FULL_PREFIX = $path;
    }
}
if ($COMPOSER_MAGENTO_FULL_PREFIX === null) {
    $MAGENTO_PATH = dirname(__DIR__ . "/../../../../../");
} else {
    $MAGENTO_PATH = substr(
        $COMPOSER_MAGENTO_FULL_PREFIX,
        0,
        strpos($COMPOSER_MAGENTO_FULL_PREFIX, "/app/code/Magento")
    );
}

$RELATIVE_TESTS_MODULE_PATH = '/Magento/FunctionalTest';

defined('MAGENTO_BP') || define('MAGENTO_BP', realpath($MAGENTO_PATH));

//Load constants from .env file
if (file_exists(MAGENTO_BP . '/dev/tests/acceptance/.env')) {
    $env = new \Dotenv\Loader(MAGENTO_BP . '/dev/tests/acceptance/.env');
    $env->load();

    if (array_key_exists('TESTS_MODULE_PATH', $_ENV) xor array_key_exists('TESTS_BP', $_ENV)) {
        throw new Exception(
            'You must define both parameters TESTS_BP and TESTS_MODULE_PATH or neither parameter'
        );
    }

    foreach ($_ENV as $key => $var) {
        defined($key) || define($key, $var);
    }

    defined('MAGENTO_CLI_COMMAND_PATH') || define(
        'MAGENTO_CLI_COMMAND_PATH',
        'dev/tests/acceptance/utils/command.php'
    );
    $env->setEnvironmentVariable('MAGENTO_CLI_COMMAND_PATH', MAGENTO_CLI_COMMAND_PATH);

    defined('MAGENTO_CLI_COMMAND_PARAMETER') || define('MAGENTO_CLI_COMMAND_PARAMETER', 'command');
    $env->setEnvironmentVariable('MAGENTO_CLI_COMMAND_PARAMETER', MAGENTO_CLI_COMMAND_PARAMETER);
}

defined('FW_BP') || define('FW_BP', realpath($FW_PATH));
defined('TESTS_BP') || define('TESTS_BP', realpath($TEST_PATH));
defined('TESTS_MODULE_PATH') || define(
    'TESTS_MODULE_PATH',
    realpath($TEST_PATH . $RELATIVE_TESTS_MODULE_PATH)
);

// add the debug flag here
$debug_mode = $_ENV['MFTF_DEBUG'] ?? false;
if (!(bool)$debug_mode && extension_loaded('xdebug')) {
    xdebug_disable();
}
