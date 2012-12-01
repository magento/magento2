<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     tools
 * @subpackage  batch_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$tests = array(
    'unit'             => array('../../tests/unit', ''),
    'unit-performance' => array('../../tests/performance/framework/tests/unit', ''),
    'unit-static'      => array('../../tests/static/framework/tests/unit', ''),
    'unit-integration' => array('../../tests/integration/framework/tests/unit', ''),
    'integration'      => array('../../tests/integration', ''),
    'static'           => array('../../tests/static', ''),
);
$arguments = getopt('', array('all'));
if (isset($arguments['all'])) {
    $tests['integration-integrity'] = array('../../tests/integration', ' testsuite/integrity');
    $tests['static'][1] = ' -c phpunit-all.xml.dist';
}

$failures = array();
foreach ($tests as $row) {
    list($dir, $options) = $row;
    $dirName = realpath(__DIR__ . '/' . $dir);
    chdir($dirName);
    $command = 'phpunit' . $options;
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
    echo "\nFAILED - " . count($failures) . ' of ' . count($tests) . ":\n";
    foreach ($failures as $message) {
        echo ' - ' . $message . "\n";
    }
} else {
    echo "\nPASSED (" . count($tests) . ")\n";
}
