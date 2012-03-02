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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$path = false;
if (isset($argv[1])) {
    if (realpath($argv[1])) {
        $path = realpath($argv[1]);
    } elseif (realpath(getcwd() . DIRECTORY_SEPARATOR . $argv[1])) {
        $path = realpath(getcwd() . DIRECTORY_SEPARATOR . $argv[1]);
    }
}

if (!$path) {
    echo "Please specify directory for scan: php -f fs_generator.php path/to/code";
    exit;
}


$basePath = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR;
$directory  = new RecursiveDirectoryIterator($path);
$iterator   = new RecursiveIteratorIterator($directory);
$regex      = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);


$map = array();
foreach ($regex as $file) {
    $filePath = str_replace('\\', '/', str_replace($basePath, '', $file[0]));
    if (strpos($filePath, 'dev') === 0 || strpos($filePath, 'shell') === 0) {
        continue;
    }

    $code = file_get_contents($file[0]);
    $tokens = token_get_all($code);

    $count    = count($tokens);
    $i        = 0;
    while ($i < $count) {
        $token = $tokens[$i];

        if (!is_array($token)) {
            $i++;
            continue;
        }

        list($id, $content, $line) = $token;

        switch ($id) {
            case T_CLASS:
            case T_INTERFACE:
                $class = '';
                do {
                    ++$i;
                    $token = $tokens[$i];
                    if (is_string($token)) {
                        continue;
                    }
                    list($type, $content, $line) = $token;
                    switch ($type) {
                        case T_STRING:
                            $class = $content;
                            break;
                    }
                } while (empty($class) && $i < $count);

                // If a classname was found, set it in the object, and
                // return boolean true (found)
                if (!empty($class)) {
                    $map[$class] = $filePath;
                }
                break;
            default:
                break;
        }
        ++$i;
    }
}

echo serialize($map);
