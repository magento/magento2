<?php
/**
 * This script (re-)builds the JS test runner config file
 */

require __DIR__ . '/../../../app/autoload.php';
require __DIR__ . '/framework/JsTestRunner.php';

JsTestRunner::fromConfigFile()->writeJsTestRunnerConfig();

