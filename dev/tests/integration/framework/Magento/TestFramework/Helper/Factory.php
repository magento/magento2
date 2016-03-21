<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Factory for helpers, used in Magento testing framework
 */
namespace Magento\TestFramework\Helper;

class Factory
{
    /**
     * @var array
     */
    protected static $_instances = [];

    /**
     * Retrieves singleton instance of helper
     *
     * @param string $name
     * @return mixed
     */
    public static function getHelper($name)
    {
        if (!isset(self::$_instances[$name])) {
            $className = preg_replace('/[^_]*$/', ucfirst($name), __CLASS__, 1);
            self::$_instances[$name] = new $className();
        }
        return self::$_instances[$name];
    }

    /**
     * Sets custom helper instance to be used for specific name, or null to clear instance.
     * Returns previous instance (if any) or null (if no helper was defined).
     *
     * @param string $name
     * @param mixed $helper
     * @return mixed
     */
    public static function setHelper($name, $helper)
    {
        $old = isset(self::$_instances[$name]) ? self::$_instances[$name] : null;
        self::$_instances[$name] = $helper;
        return $old;
    }
}
