<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreStart
/**
 * Script to operate on a test suite defined in a phpunit configuration xml or xml.dist file; split the tests
 * in the suite into groups by required size; return total number of groups or generate phpunit_<index>.xml file
 * that defines a new test suite named group_<index> with tests in group <index>
 *
 * Common scenario:
 *
 * 1. Query how many groups in a test suite with a given size --group-size=<size>
 *    php phpunitGroupConfig.php --get-total --configuration=<path-to-phpunit-xml-dist-file> --test-suite=<name> --group-size=<size> --isolate-tests=<path-to-isolate-tests-file>
 *
 * 2a. Generate the configuration file for group <index>. <index> must be in range of [1, total number of groups])
 *    php phpunitGroupConfig.php --get-group=<index> --configuration=<path-to-phpunit-xml-dist-file> --test-suite=<name> --group-size=<size> --isolate-tests=<path-to-isolate-tests-file>
 *
 * 2b. Or generate configuration files for all test groups at once
 *    php phpunitGroupConfig.php --get-group=all --configuration=<path-to-phpunit-xml-dist-file> --test-suite=<name> --group-size=<size> --isolate-tests=<path-to-isolate-tests-file>
 *
 * 3. PHPUnit command to run tests for group at <index>
 *    phpunit --configuration <path_to_phpunit_<index>.xml> --testsuite group_<index>
 */

$scriptName = basename(__FILE__);

define(
    'USAGE',
    <<<USAGE
Usage:
php -f $scriptName
    [--get-total]
        Option takes no value, when specified, script will return total number of groups for the test suite specified in --test-suite.
        It's the default if both --get-total and --get-group are specified or both --get-total and --get-group are not specified.
    [--get-group="<positive integer>|all"]
        When option takes a positive integer value <i>, script will generate phpunit_<i>.xml file in the same location as the config
        file specified in --configuration with a test suite named "group_<i>" which contains the i-th group of tests from the test
        suite specified in --test-suite.
        When option takes value "all", script will generate config files for all groups at once.
    --test-suite="<name>"
        Name of test suite to be splitted into groups.
    --group-size="<positive integer>"
        Number of tests per group.
    --configuration="<path>"
        Path to phpunit configuration xml or xml.dist file.
    [--isolate-tests="<path>"]
        Path to a text file containing tests that require group isolation. One test path per line.

Note:
Script uses getopt() which does not accept " "(space) as a separator for optional values. Use "=" for [--get-group] and [--isolate-tests] instead.
See https://www.php.net/manual/en/function.getopt.php

USAGE
);
// @codingStandardsIgnoreEnd

$options = getopt(
    '',
    [
        'get-total',
        'get-group::',
        'test-suite:',
        'group-size:',
        'configuration:',
        'isolate-tests::'
    ]
);
$requiredOpts = ['test-suite', 'group-size', 'configuration'];

try {
    foreach ($requiredOpts as $opt) {
        assertUsage(empty($options[$opt]), "Option --$opt: cannot be empty\n");
    }

    assertUsage(!ctype_digit($options['group-size']), "Option --group-size: must be positive integer\n");
    assertUsage(!realpath($options['configuration']), "Option --configuration: file doesn't exist\n");
    assertUsage(
        isset($options['isolate-tests']) && !realpath($options['isolate-tests']),
        "Option --isolate-tests: file doesn't exist\n"
    );
    $isolateTests = isset($options['isolate-tests']) ? readIsolateTests(realpath($options['isolate-tests'])) : [];

    $generateConfig = null;
    $groupIndex = null;
    if (isset($options['get-total']) || !isset($options['get-group'])) {
        $generateConfig = false;
    } else {
        assertUsage(
            (empty($options['get-group']) || !ctype_digit($options['get-group']))
            && strtolower($options['get-group']) != 'all',
            "Option --get-group: must be a positive integer or 'all'\n"
        );
        $generateConfig = true;
        $groupIndex = strtolower($options['get-group']);
    }

    $testSuite = $options['test-suite'];
    $groupSize = $options['group-size'];
    $configFile = realpath($options['configuration']);
    $workingDir = dirname($configFile) . DIRECTORY_SEPARATOR;

    $savedCwd = getcwd();
    chdir($workingDir);
    $allTests = getTestList($configFile, $testSuite);
    chdir($savedCwd);
    list($allRegularTests, $isolateTests) = fuzzyArrayDiff($allTests, $isolateTests); // diff to separate isolated tests

    $totalRegularTests = count($allRegularTests);
    if (($totalRegularTests % $groupSize) === 0) {
        $totalRegularGroups = $totalRegularTests / $groupSize;
    } else {
        $totalRegularGroups = (int)($totalRegularTests / $groupSize) + 1;
    }
    $totalGroups = $totalRegularGroups + count($isolateTests);
    assertUsage(
        $totalGroups == 0,
        "Option --test-suite: no test found for test suite '{$testSuite}'\n"
    );

    if (!$generateConfig) {
        print $totalGroups;
        exit(0);
    }

    if ($groupIndex == 'all') {
        $sIndex = 1;
        $eIndex = $totalGroups;
    } else {
        assertUsage(
            (int)$groupIndex > $totalGroups,
            "Option --get-group: can not be greater than $totalGroups\n"
        );
        $sIndex = (int)$groupIndex;
        $eIndex = $sIndex;
    }

    $successMsg = "PHPUnit configuration files created:\n";
    for ($index = $sIndex; $index < $eIndex + 1; $index++) {
        $groupTests = [];
        if ($index <= $totalRegularGroups) {
            $groupTests = array_chunk($allRegularTests, $groupSize)[$index - 1];
        } else {
            $groupTests[] = $isolateTests[$index - $totalRegularGroups - 1];
        }

        $groupConfigFile = $workingDir . 'phpunit_' . $index . '.xml';
        createGroupConfig($configFile, $groupConfigFile, $groupTests, $index);
        $successMsg .= "{$groupConfigFile}, group: {$index}, test suite: group_{$index}\n";
    }
    print $successMsg;

} catch (Exception $e) {
    print $e->getMessage();
    exit(1);
}

/**
 * Generate a phpunit configuration file for a given group
 *
 * @param string  $in
 * @param string  $out
 * @param array   $group
 * @param integer $index
 * @return void
 * @throws Exception
 */
function createGroupConfig($in, $out, $group, $index)
{
    $beforeTestSuites = true;
    $afterTestSuites = false;
    $outLines = '';
    $inLines = explode("\n", file_get_contents($in));
    foreach ($inLines as $inLine) {
        if ($beforeTestSuites) {
            // Replacing existing <testsuites> node with new <testsuites> node
            preg_match('/<testsuites/', $inLine, $bMatch);
            if (isset($bMatch[0])) {
                $beforeTestSuites = false;
                $outLines .= getFormattedGroup($group, $index);
                continue;
            }
        }
        if (!$afterTestSuites) {
            preg_match('/<\/\s*testsuites/', $inLine, $aMatch);
            if (isset($aMatch[0])) {
                $afterTestSuites = true;
                continue;
            }
        }
        if ($beforeTestSuites) {
            // Adding new <testsuites> node right before </phpunit> if there is no existing <testsuites> node
            preg_match('/<\/\s*phpunit/', $inLine, $lMatch);
            if (isset($lMatch[0])) {
                $outLines .= getFormattedGroup($group, $index);
                $outLines .= $inLine . "\n";
                break;
            }
        }
        if ($beforeTestSuites || $afterTestSuites) {
            $outLines .= $inLine . "\n";
        }
    }
    file_put_contents($out, $outLines);
}

/**
 * Format tests in an array into <testsuite> node defined by phpunit xml schema
 *
 * @param array   $group
 * @param integer $index
 * @return string
 */
function getFormattedGroup($group, $index)
{
    $output = "\t<testsuites>\n";
    $output .= "\t\t<testsuite name=\"group_{$index}\">\n";
    foreach ($group as $ch) {
        $output .= "\t\t\t<file>{$ch}</file>\n";
    }
    $output .= "\t\t</testsuite>\n";
    $output .= "\t</testsuites>\n";
    return $output;
}

/**
 * Return paths for all tests as an array for a given test suite in a phpunit.xml(.dist) file
 *
 * @param string $configFile
 * @param string $suiteName
 * @return array
 */
function getTestList($configFile, $suiteName)
{
    $testCases = [];
    $config = simplexml_load_file($configFile);
    foreach ($config->xpath('//testsuite') as $testsuite) {
        if (strtolower((string)$testsuite['name']) != strtolower($suiteName)) {
            continue;
        }
        foreach ($testsuite->file as $file) {
            $testCases[(string)$file] = true;
        }
        $excludeFiles = [];
        foreach ($testsuite->exclude as $excludeFile) {
            $excludeFiles[] = (string)$excludeFile;
        }
        foreach ($testsuite->directory as $directoryPattern) {
            foreach (glob($directoryPattern, GLOB_ONLYDIR) as $directory) {
                if (!file_exists((string)$directory)) {
                    continue;
                }
                $suffix = isset($directory['suffix']) ? (string)$directory['suffix'] : 'Test.php';
                $fileIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator((string)$directory));
                foreach ($fileIterator as $fileInfo) {
                    $pathToTestCase = (string)$fileInfo;
                    if (substr_compare($pathToTestCase, $suffix, -strlen($suffix)) === 0
                        && !isTestClassAbstract($pathToTestCase)
                    ) {
                        $inExclude = false;
                        foreach ($excludeFiles as $excludeFile) {
                            if (strpos($pathToTestCase, $excludeFile) !== false) {
                                $inExclude = true;
                                break;
                            }
                        }
                        if (!$inExclude) {
                            $testCases[$pathToTestCase] = true;
                        }
                    }
                }
            }
        }
    }
    $testCases = array_keys($testCases); // automatically avoid file duplications
    sort($testCases);
    return $testCases;
}

/**
 * Determine if a file contains an abstract class
 *
 * @param string $testClassPath
 * @return bool
 */
function isTestClassAbstract($testClassPath)
{
    return strpos(file_get_contents($testClassPath), "\nabstract class") !== false;
}

/**
 * Return isolation tests as an array by reading from a file
 *
 * @param string $file
 * @return array
 */
function readIsolateTests($file)
{
    $tests = [];
    $lines = explode("\n", file_get_contents($file));
    foreach ($lines as $line) {
        if (!empty(trim($line)) && substr_compare(trim($line), '#', 0, 1) !== 0) {
            $tests[] = trim($line);
        }
    }
    return $tests;
}

/**
 * Array diff based on partial match
 *
 * @param array $oArray
 * @param array $dArray
 * @return array
 */
function fuzzyArrayDiff($oArray, $dArray)
{
    $ret1 = [];
    $ret2 = [];
    foreach ($oArray as $obj) {
        $ret1[] = $obj;
        foreach ($dArray as $diff) {
            if (stripos($obj, $diff) !== false) {
                $ret2[] = $obj;
                array_pop($ret1);
                break;
            }
        }
    }
    return [$ret1, $ret2];
}

/**
 * Assert usage by throwing exception on condition evaluating to true
 *
 * @param bool $condition
 * @param string $error
 * @throws Exception
 */
function assertUsage($condition, $error)
{
    if ($condition) {
        $error .= "\n" . USAGE;
        throw new Exception($error);
    }
}
