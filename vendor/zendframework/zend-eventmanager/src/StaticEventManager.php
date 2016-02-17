<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

/**
 * Static version of EventManager
 */
class StaticEventManager extends SharedEventManager
{
    /**
     * @var SharedEventManagerInterface
     */
    protected static $instance;

    /**
     * Singleton
     */
    protected function __construct()
    {
    }

    /**
     * Singleton
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Retrieve instance
     *
     * @return StaticEventManager
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::setInstance(new static());
        }
        return static::$instance;
    }

    /**
     * Set the singleton to a specific SharedEventManagerInterface instance
     *
     * @param SharedEventManagerInterface $instance
     * @return void
     */
    public static function setInstance(SharedEventManagerInterface $instance)
    {
        static::$instance = $instance;
    }

    /**
     * Is a singleton instance defined?
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return (static::$instance instanceof SharedEventManagerInterface);
    }

    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$instance = null;
    }
}
