<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

require_once __DIR__ . '/../../../app/autoload.php';

$scriptName = basename(__FILE__);

define(
    'USAGE',
    <<<USAGE
Usage:
php -f $scriptName path_to_phpunit.xml(.dist) rest|soap|graphql|integration
USAGE
);

try {
    assertUsage(empty($argv[1]) || !file_exists($argv[1]), 'Invalid $argv[1]: must be a phpunit.xml(.dist) file');
    $xmlDom = new DOMDocument();
    $xmlDom->preserveWhiteSpace = true;
    $xmlDom->formatOutput = true;
    assertUsage($xmlDom->load($argv[1]) == false, 'Invalid $argv[1]: must be a phpunit.xml(.dist) file');
    $testType = !empty($argv[2]) ? getTestType($argv[2]) : null;
    assertUsage(empty($testType), 'Invalid $argv[2]: must be a value from "rest", "soap", "graphql" or "integration"');

    // This flag allows the user to skip generating default test suite directory in result <testsuite> node.
    // This is desired for internal api-functional builds.
    $skipDefaultDir = !empty($argv[3]);

    // Update testsuite based on magento installation
    $xmlDom = updateTestSuite($xmlDom, $testType);
    $xmlDom->save($argv[1]);
    //phpcs:ignore Magento2.Security.LanguageConstruct
    print("{$testType} " . basename($argv[1]) . " is updated.");
    //phpcs:ignore Magento2.Security.LanguageConstruct
} catch (Exception $e) {
    //phpcs:ignore Magento2.Security.LanguageConstruct
    print($e->getMessage());
    //phpcs:ignore Magento2.Security.LanguageConstruct
    exit(1);
}

/**
 * Parse input string to get test type.
 *
 * @param String $arg
 * @return string
 */
function getTestType(String $arg): string
{
    $testType = null;
    switch (strtolower(trim($arg))) {
        case 'rest':
            $testType = 'REST';
            break;
        case 'soap':
            $testType = 'SOAP';
            break;
        case 'graphql':
            $testType = 'GraphQl';
            break;
        case 'integration':
            $testType = 'Integration';
            break;
        default:
            break;
    }
    return $testType;
}

/**
 * Find magento modules directories patterns through magento ComponentRegistrar.
 *
 * @param string $testType
 * @return array
 */
function findMagentoModuleDirs(string $testType): array
{
    $patterns = [
        'Integration' => 'Integration',
        'REST' => 'Api',
        'SOAP' => 'Api',
        'GraphQl' => 'GraphQl'
    ];
    $magentoBaseDir = realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR;
    $magentoBaseDirPattern = preg_quote($magentoBaseDir, '/');
    $componentRegistrar = new ComponentRegistrar();
    $modulePaths = $componentRegistrar->getPaths(ComponentRegistrar::MODULE);
    $directoryPatterns = [];
    $excludePatterns = [];
    foreach ($modulePaths as $modulePath) {
        preg_match('~' . $magentoBaseDirPattern . '(.+)\/[^\/]+~', $modulePath, $match);
        if (isset($match[1]) && isset($patterns[$testType])) {
            $directoryPatterns[] = '../../../' . $match[1] . '/*/Test/' . $patterns[$testType];
            if ($testType == 'GraphQl') {
                $directoryPatterns[] = '../../../' . $match[1] . '/*GraphQl/Test/Api';
                $directoryPatterns[] = '../../../' . $match[1] . '/*graph-ql/Test/Api';
            } elseif ($testType == 'REST' || $testType == 'SOAP') {
                $excludePatterns[] = '../../../' . $match[1] . '/*/Test/' . $patterns['GraphQl'];
                $excludePatterns[] = '../../../' . $match[1] . '/*GraphQl/Test/Api';
                $excludePatterns[] = '../../../' . $match[1] . '/*graph-ql/Test/Api';
            }
        }
    }

    return [
        'directory' => array_unique($directoryPatterns),
        'exclude' => array_unique($excludePatterns)
    ];
}

/**
 * Create a new testsuite DOMDocument based on installed magento module directories.
 *
 * @param string $testType
 * @param string $attribute
 * @param array  $excludes
 * @return DOMDocument
 * @throws DOMException
 */
function createNewDomElement(string $testType, string $attribute, array $excludes): DOMDocument
{
    $defTestSuite = getDefaultSuites($testType);

    // Create the new element
    $newTestSuite = new DomDocument();
    $newTestSuite->formatOutput = true;
    $newTestSuiteElement = $newTestSuite->createElement('testsuite');
    $newTestSuiteElement->setAttribute('name', $attribute);
    foreach ($defTestSuite['directory'] as $directory) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('directory', $directory));
    }

    $moduleDirs = findMagentoModuleDirs($testType);
    foreach ($moduleDirs['directory'] as $directory) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('directory', $directory));
    }
    foreach ($defTestSuite['exclude'] as $defExclude) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('exclude', $defExclude));
    }
    foreach ($moduleDirs['exclude'] as $modExclude) {
        $newTestSuiteElement->appendChild($newTestSuite->createElement('exclude', $modExclude));
    }
    foreach ($excludes as $exclude) {
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
    /** @var DOMNode $node */
    foreach ($nodelist as $node) {
        $attribute = $node->getAttribute('name');
        if (stripos($attribute, 'real') !== false) {
            $excludes = [];
            $excludeList = $node->getElementsByTagName('exclude');
            /** @var DOMNode $excludeNode */
            foreach ($excludeList as $excludeNode) {
                $excludes[] = $excludeNode->textContent;
            }
            // Load the $parent document fragment into the current document
            $newNode = $dom->importNode(
                createNewDomElement($testType, $attribute, $excludes)->documentElement,
                true
            );
            // Replace
            $node->parentNode->replaceChild($newNode, $node);
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
    global $skipDefaultDir;

    $suites = [];
    switch ($testType) {
        case 'Integration':
            $suites = [
                'directory' => [
                    'testsuite'
                ],
                'exclude' => [
                    'testsuite/Magento/MemoryUsageTest.php'
                ]
            ];
            break;
        case 'REST':
        case 'SOAP':
            $suites = [
                'directory' => $skipDefaultDir ? [] : ['testsuite'],
                'exclude' => [
                    'testsuite/Magento/GraphQl'
                ]
            ];
            break;
        case 'GraphQl':
            $suites = [
                'directory' => $skipDefaultDir ? [] : ['testsuite/Magento/GraphQl'],
                'exclude' => [
                ]
            ];
    }
    return $suites;
}
