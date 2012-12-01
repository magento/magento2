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
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require __DIR__ . '/../../../../app/autoload.php';
Magento_Autoload_IncludePath::addIncludePath(array(
    __DIR__,
    realpath(__DIR__ . '/../../../tests/static/framework')
));

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

$workingDir = __DIR__;
if (isset($options['w'])) {
    $workingDir = $options['w'];
}

$wordsFinder = new SanityWordsFinder($configFile, $workingDir);

$verbose = isset($options['v']) ? true : false;
if ($verbose) {
    $words = $wordsFinder->getSearchedWords();
    printf('Searching for banned words: "%s"...', implode('", "', $words));
}

$found = $wordsFinder->findWordsRecursively();
if ($found) {
    echo "Found banned words in the following files:\n";
    foreach ($found as $info) {
        echo $info['file'] . ' - "' . implode('", "', $info['words']) . "\"\n";
    }
    exit(1);
}

if ($verbose) {
    echo "No banned words found in the source code.\n";
}
exit(0);
