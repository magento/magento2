<?php
/**
 * Batch tool for running all or some of tests
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$vendorDir = require '../../app/etc/vendor_path.php';

$commands = [
    'unit'                   => ['../tests/unit', ''],
    'unit-performance'       => ['../tests/performance/framework/tests/unit', ''],
    'unit-static'            => ['../tests/static/framework/tests/unit', ''],
    'unit-integration'       => ['../tests/integration/framework/tests/unit', ''],
    'integration'            => ['../tests/integration', ''],
    'integration-integrity'  => ['../tests/integration', ' testsuite/Magento/Test/Integrity'],
    'static-default'         => ['../tests/static', ''],
    'static-legacy'          => ['../tests/static', ' testsuite/Magento/Test/Legacy'],
    'static-integration-php' => ['../tests/static', ' testsuite/Magento/Test/Php/Exemplar'],
    'static-integration-js'  => ['../tests/static', ' testsuite/Magento/Test/Js/Exemplar'],
];
$types = [
    'all'             => array_keys($commands),
    'unit'            => ['unit', 'unit-performance', 'unit-static', 'unit-integration'],
    'integration'     => ['integration'],
    'integration-all' => ['integration', 'integration-integrity'],
    'static'          => ['static-default'],
    'static-all'      => ['static-default', 'static-legacy', 'static-integration-php', 'static-integration-js'],
    'integrity'       => ['static-default', 'static-legacy', 'integration-integrity'],
    'legacy'          => ['static-legacy'],
    'default'         => [
        'unit', 'unit-performance', 'unit-static', 'unit-integration', 'integration', 'static-default',
    ],
];

$arguments = getopt('', ['type::']);
if (!isset($arguments['type'])) {
    $arguments['type'] = 'default';
} elseif (!isset($types[$arguments['type']])) {
    echo "Invalid type: '{$arguments['type']}'. Available types: " . implode(', ', array_keys($types)) . "\n\n";
    exit(1);
}

$failures = [];
$runCommands = $types[$arguments['type']];
foreach ($runCommands as $key) {
    list($dir, $options) = $commands[$key];
    $dirName = realpath(__DIR__ . '/' . $dir);
    chdir($dirName);
    $command = realpath(__DIR__ . '/../../') . '/' . $vendorDir . '/phpunit/phpunit/phpunit' . $options;
    $message = $dirName . '> ' . $command;
    echo "\n\n";
    echo str_pad("---- {$message} ", 70, '-');
    echo "\n\n";
    passthru($command, $returnVal);
    if ($returnVal) {
        $failures[] = $message;
    }
}

echo "\n" , str_repeat('-', 70), "\n";
if ($failures) {
    echo "\nFAILED - " . count($failures) . ' of ' . count($runCommands) . ":\n";
    foreach ($failures as $message) {
        echo ' - ' . $message . "\n";
    }
} else {
    echo "\nPASSED (" . count($runCommands) . ")\n";
}
