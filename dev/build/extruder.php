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
 * @package    extruder
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../lib/Magento/Shell.php';
require_once __DIR__ . '/../../lib/Magento/Exception.php'; // used by Magento_Shell (autoload is not present here)

define('USAGE', <<<USAGE
$>./extruder.php -w <working_dir> -l /path/to/list.txt [[-l /path/to/extra.txt] parameters]
    additional parameters:
    -w dir  directory with working copy to edit with the extruder
    -l      one or many files with lists that refer to files and directories to be deleted
    -v      additional verbosity in output

USAGE
);

$options = getopt('w:l:v');

try {
    // working dir argument
    if (empty($options['w'])) {
        throw new Exception(USAGE);
    }
    $workingDir = realpath($options['w']);
    if (!$workingDir || !is_writable($workingDir) || !is_dir($workingDir)) {
        throw new Exception("'{$options['w']}' must be a writable directory.");
    }

    // lists argument
    if (empty($options['l'])) {
        throw new Exception(USAGE);
    }
    if (!is_array($options['l'])) {
        $options['l'] = array($options['l']);
    }
    $list = array();
    foreach ($options['l'] as $file) {
        if (!is_file($file) || !is_readable($file)) {
            throw new Exception("Specified file with patterns does not exist or cannot be read: '{$file}'");
        }
        $patterns = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($patterns as $pattern) {
            if (empty($pattern) || 0 === strpos($pattern, '#')) { // comments start from #
                continue;
            }
            $pattern = $workingDir . DIRECTORY_SEPARATOR . $pattern;
            $items = glob($pattern, GLOB_BRACE);
            if (empty($items)) {
                throw new Exception("glob() pattern '{$pattern}' returned empty result.");
            }
            $list = array_merge($list, $items);
        }
    }
    if (empty($list)) {
        throw new Exception('List of files or directories to delete is empty.');
    }

    // verbosity argument
    $verbose = isset($options['v']);

    // perform "extrusion"
    $shell = new Magento_Shell($verbose);
    foreach ($list as $item) {
        if (!file_exists($item)) {
            throw new Exception("The file or directory '{$item} is marked for deletion, but it doesn't exist.");
        }
        $shell->execute(
            'git --git-dir %s --work-tree %s rm -r -f -- %s',
            array("{$workingDir}/.git", $workingDir, $item)
        );
        if (file_exists($item)) {
            throw new Exception("The file or directory '{$item}' was supposed to be deleted, but it still exists.");
        }
    }

    exit(0);
} catch (Exception $e) {
    if ($e->getPrevious()) {
        $message = (string)$e->getPrevious();
    } else {
        $message = $e->getMessage();
    }
    echo $message . PHP_EOL;
    exit(1);
}
