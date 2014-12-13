<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
