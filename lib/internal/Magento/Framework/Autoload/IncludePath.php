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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Autoload;

class IncludePath
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
    public function getFile($class)
    {
        $relativePath = $this->getFilePath($class);
        return stream_resolve_include_path($relativePath);
    }

    /**
     * Get relative file path for specified class
     *
     * @static
     * @param string $class
     * @return string
     */
    public function getFilePath($class)
    {
        if (strpos($class, self::NS_SEPARATOR) !== false) {
            $class = ltrim(str_replace(self::NS_SEPARATOR, '_', $class), '_');
        }
        return str_replace('_', '/', $class) . '.php';
    }

    /**
     * Add specified path(s) to the current include_path
     *
     * @param string|array $path
     * @param bool $prepend Whether to prepend paths or to append them
     * @return void
     */
    public function addIncludePath($path, $prepend = true)
    {
        $includePathExtra = implode(PATH_SEPARATOR, (array)$path);
        $includePath = get_include_path();
        $pathSeparator = $includePath && $includePathExtra ? PATH_SEPARATOR : '';
        if ($prepend) {
            $includePath = $includePathExtra . $pathSeparator . $includePath;
        } else {
            $includePath = $includePath . $pathSeparator . $includePathExtra;
        }
        set_include_path($includePath);
    }

    /**
     * Resolve a class file and include it
     *
     * @param string $class
     * @return void
     */
    public function load($class)
    {
        $file = $this->getFile($class);
        if ($file) {
            include $file;
        }
    }
}
