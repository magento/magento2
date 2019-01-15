<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Generate Allure report using Allure Commandline (CLI).
 *
 * Allure CLI is a Java application so it's available for all platforms.
 * You have to manually install Java 1.7+ before using Allure CLI.
 * Information on how to install Allure CLI can be found at:
 * http://wiki.qatools.ru/display/AL/Allure+Commandline
 */

// Explicitly define Allure CLI executable if it's not available in your PATH.
define('ALLURE_CLI', 'allure');

$mtfRoot = dirname(dirname(__FILE__));
$mtfRoot = str_replace('\\', '/', $mtfRoot);
define('MTF_BP', $mtfRoot);

// Allure test results directory which needs to match what's defined in phpunit.xml.
$allureResultsDir = MTF_BP . '/var/allure-results/';
// Allure report directory.
$allureReportDir = MTF_BP . '/var/allure-report/';

// Generate report using Allure CLI.
exec(ALLURE_CLI . ' generate ' . $allureResultsDir . ' -o '. $allureReportDir);

// Open report using Allure CLI.
exec(ALLURE_CLI . ' report open --report-dir ' . $allureReportDir);
