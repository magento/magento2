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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\Helper;

/**
 * Class Cli static helper
 *
 */
class Cli
{
    /**
     * Getopt object
     *
     * @var \Zend_Console_Getopt
     */
    protected static $_getopt;

    /**
     * Set GetOpt object
     *
     * @param \Zend_Console_Getopt $getopt
     */
    public static function setOpt(\Zend_Console_Getopt $getopt)
    {
        static::$_getopt = $getopt;
    }

    /**
     * Get option value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed|null
     */
    public static function getOption($key, $default = null)
    {
        if (!static::$_getopt instanceof \Zend_Console_Getopt) {
            return $default;
        }
        $value = static::$_getopt->getOption($key);
        if (is_null($value)) {
            return $default;
        }
        return $value;
    }
}
