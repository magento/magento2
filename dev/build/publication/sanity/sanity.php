#!/usr/bin/php
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
 * @category   build
 * @package    sanity
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require __DIR__ . '/SanityRoutine.php';

define('USAGE', <<<USAGE
php -f sanity.php -c <config_file> [-w <dir>] [-v]
    -c <config_file> path to configuration file with rules and white list
    [-w <dir>]       use specified working dir instead of current
    [-v]             verbose mode
USAGE
);

$shortOpts = 'c:w:v';
$options = getopt($shortOpts);

if (!isset($options['c'])) {
    print USAGE;
    exit(1);
}

$configFile = $options['c'];
if (!file_exists($configFile)) {
    print 'File "' . $configFile . '" does not exist (current dir is "' . getcwd() . '").' . "\n";
    exit(1);
}

$config = SanityRoutine::loadConfig($configFile);
if (!$config) {
    print "Problem with config file\n";
    exit(1);
}
if (!$config['words']) {
    print "No words to check\n";
    exit(1);
}

$workingDir = dirname(__FILE__);
if (isset($options['w'])) {
    $workingDir = $options['w'];
}
$workingDir = rtrim($workingDir, '/\\');
if (!is_dir($workingDir)) {
    print 'Working dir "' . $workingDir . '" does not exist' . "\n";
    exit(1);
}

$verbose = isset($options['v']) ? true : false;
SanityRoutine::$verbose = $verbose;

SanityRoutine::printVerbose(sprintf('Searching for banned words: "%s"...', implode('", "', $config['words'])));

$found = SanityRoutine::findWords(realpath($workingDir), realpath($workingDir), $config);
if ($found) {
    echo "Found banned words in the following files:\n";
    foreach ($found as $info) {
        echo $info['file'] . ' - "' . implode('", "', $info['words']) . "\"\n";
    }
    exit(1);
}
SanityRoutine::printVerbose('No banned words found in the source code.' . "\n");
exit(0);
