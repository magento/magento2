<?php
/**
 * Automated replacement of license notice into placeholders
 *
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
 * @category Magento
 * @package tools
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$sourceDir = realpath(__DIR__ . '/../../..');

// scan for files (split up into several calls to overcome maximum limitation of 260 chars in glob pattern)
$files = globDir($sourceDir, '*.{xml,xml.template,xml.additional,xml.dist,xml.sample,xsd,mxml}', GLOB_BRACE);
$files = array_merge($files, globDir($sourceDir, '*.{php,php.sample,phtml,html,htm,css,js,as,sql}', GLOB_BRACE));

// exclude files from blacklist
$blacklist = require __DIR__ . '/blacklist.php';
foreach ($blacklist as $item) {
    $excludeDirs = glob("{$sourceDir}/{$item}", GLOB_ONLYDIR) ?: array();
    foreach ($excludeDirs as $excludeDir) {
        foreach ($files as $i => $file) {
            if (0 === strpos($file, $excludeDir)) {
                unset($files[$i]);
            }
        }
    }
    if (!$excludeDirs) {
        $excludeFiles = glob("{$sourceDir}/{$item}", GLOB_BRACE) ?: array();
        foreach ($excludeFiles as $excludeFile) {
            $i = array_search($excludeFile, $files);
            if (false !== $i) {
                unset($files[$i]);
            }
        }
    }
}

// replace
$licensePlaceholder = ' * {license}' . "\n";
$replacements = array(
    array('/\s\*\sMagento.+?NOTICE OF LICENSE.+?DISCLAIMER.+?@/s', $licensePlaceholder . " *\n * @"),
    array('/\ \*\ \{license_notice\}\s/s', $licensePlaceholder),
);
foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = $content;
    foreach ($replacements as $row) {
        list($regex, $replacement) = $row;
        $newContent = preg_replace($regex, $replacement, $content);
        if ($newContent != $content) {
            break;
        }
    }
    $newContent = preg_replace('/^\s\*\s@copyright.+?$/m', '', $newContent);
    $newContent = preg_replace('/^\s\*\s@license.+$/m', '', $newContent);
    $newContent = preg_replace('/(\{license\}.+?)\n\n\ \*/s', '\\1' . " *", $newContent);
    if ($newContent != $content) {
        file_put_contents($file, $newContent);
    }
}

/**
 * Perform a glob search in specified directory
 *
 * @param string $dir
 * @param string $filesPattern
 * @param int $flags
 * @return array
 */
function globDir($dir, $filesPattern, $flags)
{
    if (!$dir || !is_dir($dir)) {
        return array();
    }
    $result = glob($dir . '/' . $filesPattern, $flags) ?: array();
    $dirs = glob($dir . '/*', GLOB_ONLYDIR) ?: array();
    foreach ($dirs as $innerDir) {
        $result = array_merge($result, globDir($innerDir, $filesPattern, $flags));
    }
    return $result;
}
