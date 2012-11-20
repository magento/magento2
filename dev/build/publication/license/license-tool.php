#!/usr/bin/php
<?php
/**
 * {license_notice}
 *
 * @category   build
 * @package    license
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Command line tool for processing file docblock of Magento source code files.
 */

require dirname(__FILE__) . '/Routine.php';
require dirname(__FILE__) . '/LicenseAbstract.php';

define('USAGE', <<<USAGE
php -f license-tool.php -- -e <edition> [-w <dir>] [-v] [-d] [-0]
    -e <edition> name of product edition (see "conf" directory relatively to this script)
    -w <dir>     use specified working dir instead of current
    -v           verbose output
    -d           dry run
    -0           exit with a zero status even when not all replacements have succeeded

USAGE
);

$options = getopt('e:w:vd0');

if (!isset($options['e'])) {
    print USAGE;
    exit(1);
}

if (isset($options['v'])) {
    Routine::$isVerbose = true;
}

$dryRun = false;
if (isset($options['d'])) {
    Routine::$dryRun = true;
}

$workingDir = '.';
if (isset($options['w'])) {
    $workingDir = rtrim($options['w'], DIRECTORY_SEPARATOR);
}
if (!is_dir($workingDir)) {
    Routine::printLog("Directory '{$workingDir}' does not exist.\n");
    exit(1);
}

$config = require __DIR__ . "/conf/{$options['e']}.php";
$blackList = require __DIR__ . '/../../../../dev/tools/license_placeholder/blacklist.php';

try {
    Routine::run($workingDir, $config, $blackList);
} catch(Exception $e) {
    Routine::printLog($e->getMessage());
    exit(isset($options['0']) ? 0 : 1);
}
