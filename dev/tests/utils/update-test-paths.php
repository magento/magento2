<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Component\ComponentRegistrar;

require_once __DIR__ . '/../../../app/autoload.php';

$scriptName = basename(__FILE__);

define(
    'USAGE',
    <<<USAGE
Usage:
php -f $scriptName path_to_phpunit.xml(.dist)
USAGE
);

assertUsage(empty($argv[1]) || !file_exists($argv[1]), 'missing or invalid phpunit.xml(.dist) file');
$xmlDom = new DOMDocument();
$xmlDom->preserveWhiteSpace = true;
$xmlDom->formatOutput = true;
assertUsage($xmlDom->load($argv[1]) == false, 'missing or invalid phpunit.xml(.dist) file');
$testType = getTestType($xmlDom);
// Update testsuite based on magento installation
$xmlDom = updateTestSuite($xmlDom, $testType);
//$xmlDom->save($argv[1]); //Uncomment after review
$xmlDom->save($argv[1] . '.new');
echo "{$testType} " . basename($argv[1]) . " is updated.";

/**
 * Read DOMDocument to get test type.
 *
 * @param DOMDocument $dom
 * @return string
 */
function getTestType(DOMDocument $dom): string
{
    $testType = null;
    /** @var DOMElement $testsuite */
    //$testsuite = null;
    foreach ($dom->getElementsByTagName('testsuite') as $testsuite) {
        if (stripos($testsuite->getAttribute('name'), 'real suite') === false) {
            continue;
        }
        if (stripos($testsuite->getAttribute('name'), 'rest') !== false) {
            $testType = 'REST';
        }
        if (stripos($testsuite->getAttribute('name'), 'soap') !== false) {
            $testType = 'SOAP';
        }
        if (stripos($testsuite->getAttribute('name'), 'graphql') !== false) {
            $testType = 'GraphQL';
        }
        if (stripos($testsuite->getAttribute('name'), 'integration') !== false) {
            $testType = 'Integration';
        }
        if ($testType) {
            break;
        }
    }
    return $testType;
}

/**
 * Find magento modules directories patterns through magento ComponentRegistrar.
 *
 * @param string $testType
 * @return string []
 */
function findMagentoModuleDirs(string $testType): array
{
    $patterns = [
        'Integration' => 'Integration',
        'REST' => 'Api',
        'SOAP' => 'Api',
        // Is there a path pattern for 'GraphQL'?
    ];
    $magentoBaseDir = realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR;
    $magentoBaseDirPattern = preg_quote($magentoBaseDir, '/');
    $componentRegistrar = new ComponentRegistrar();
    $modulePaths = $componentRegistrar->getPaths(ComponentRegistrar::MODULE);
    $testPathPatterns = [];
    foreach ($modulePaths as $modulePath) {
        preg_match('~' . $magentoBaseDirPattern . '(.+)\/[^\/]+~', $modulePath, $match);
        if (isset($match[1]) && isset($patterns[$testType])) {
            $testPathPatterns[] = '../../../' . $match[1] . '/*/Test/' . $patterns[$testType];
        }
    }

    return array_unique($testPathPatterns);
}

/**
 * Create a new testsuite DOMDocument based on installed magento module directories.
 *
 * @param string $testType
 * @return DOMDocument
 * @throws DOMException
 */
function createNewDomElement(string $testType): DOMDocument
{
    $defTestSuite = getDefaultSuites($testType);

    // Create the new element
    $newTestSuite = new DomDocument();
    $newTestSuite->formatOutput = true;
    $newTestSuiteElement = $newTestSuite->createElement('testsuite');
    if ($testType == 'Integration') {
        $newTestSuiteElement->setAttribute('name', 'Magento ' . $testType . ' Tests Real Suite');
    } else {
        $newTestSuiteElement->setAttribute('name', 'Magento ' . $testType . ' Web API Functional Tests Real Suite');
    }
    foreach ($defTestSuite['directory'] as $directory) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('directory', $directory));
    }
    foreach (findMagentoModuleDirs($testType) as $directory) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('directory', $directory));
    }
    foreach ($defTestSuite['exclude'] as $exclude) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('exclude', $exclude));
    }

    $newTestSuite->appendChild($newTestSuiteElement);
    return $newTestSuite;
}

/**
 * Replace testsuite node with created new testsuite node in dom document passed in.
 *
 * @param DOMDocument $dom
 * @param string      $testType
 * @return DOMDocument
 * @throws DOMException
 */
function updateTestSuite(DOMDocument $dom, string $testType): DOMDocument
{
    // Locate the old node
    $xpath = new DOMXpath($dom);
    $nodelist = $xpath->query('/phpunit/testsuites/testsuite');
    for ( $index = 0; $index < $nodelist->count(); $index++) {
        $oldNode = $nodelist->item($index);
        if (stripos($oldNode->getAttribute('name'), 'real suite') !== false) {
            // Load the $parent document fragment into the current document
            $newNode = $dom->importNode(createNewDomElement($testType)->documentElement, true);
            // Replace
            $oldNode->parentNode->replaceChild($newNode, $oldNode);
        }
    }
    return $dom;
}

/**
 * Assert usage by throwing exception on condition evaluating to true
 *
 * @param bool   $condition
 * @param string $error
 * @throws Exception
 */
function assertUsage(bool $condition, string $error): void
{
    if ($condition) {
        $error .= "\n" . USAGE;
        throw new Exception($error);
    }
}

/**
 * Return suite default directories and excludes for a given test type.
 *
 * @param string $testType
 * @return array
 */
function getDefaultSuites(string $testType): array
{
    $suites = [];
    switch ($testType) {
        case 'Integration':
            $suites = [
                'directory' => [
                    'testsuite'
                ],
                'exclude' => [
                    'testsuite/Magento/MemoryUsageTest.php',
                    'testsuite/Magento/IntegrationTest.php'
                ]
            ];
            break;
        case 'REST':
            $suites = [
                'directory' => [
                    'testsuite'
                ],
                'exclude' => [
                    'testsuite/Magento/GraphQl'
                ]
            ];
            break;
        case 'SOAP':
            $suites = [
                'directory' => [
                    'testsuite'
                ],
                'exclude' => [
                ]
            ];
            break;
        case 'GraphQL':
            $suites = [
                'directory' => [
                    'testsuite/Magento/GraphQl'
                ],
                'exclude' => [
                ]
            ];
    }
    return $suites;
}
