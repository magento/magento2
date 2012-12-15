<?php
/**
 * An autoloader that uses include path. Compliant with PSR-0 standard
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
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Autoload_IncludePath
{
    /**
     * Namespaces separator
     */
    const NS_SEPARATOR = '\\';

    /**
     * Find a file in include path
     *
     * @param string $class
     * @return string|bool
     */
    public static function getFile($class)
    {
        if (strpos($class, self::NS_SEPARATOR) !== false) {
            $class = ltrim(str_replace(self::NS_SEPARATOR, '_', $class), '_');
        }
        $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        return stream_resolve_include_path($relativePath);
    }

    /**
     * Append specified path(s) to include_path
     *
     * @param string|array $path
     */
    public static function addIncludePath($path)
    {
        $result = implode(PATH_SEPARATOR, (array)$path);
        $includePath = get_include_path();
        if ($includePath) {
            $result = $result . PATH_SEPARATOR . $includePath;
        }
        set_include_path($result);
    }

    /**
     * Resolve a class file and include it
     *
     * @param $class
     */
    public static function load($class)
    {
        $file = self::getFile($class);
        if ($file) {
            include $file;
        }
    }
}
