<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Placeholder;

use Zend\View\Exception;

/**
 * Registry for placeholder containers
 */
class Registry
{
    /**
     * Singleton instance
     *
     * @var Registry
     */
    protected static $instance;

    /**
     * Default container class
     *
     * @var string
     */
    protected $containerClass = 'Zend\View\Helper\Placeholder\Container';

    /**
     * Placeholder containers
     *
     * @var array
     */
    protected $items = array();

    /**
     * Retrieve or create registry instance
     *
     * @return Registry
     */
    public static function getRegistry()
    {
        trigger_error('Placeholder view helpers should no longer use a singleton registry', E_USER_DEPRECATED);
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Unset the singleton
     *
     * Primarily useful for testing purposes; sets {@link $instance} to null.
     *
     * @return void
     */
    public static function unsetRegistry()
    {
        trigger_error('Placeholder view helpers should no longer use a singleton registry', E_USER_DEPRECATED);
        static::$instance = null;
    }

    /**
     * Set the container for an item in the registry
     *
     * @param  string                      $key
     * @param  Container\AbstractContainer $container
     * @return Registry
     */
    public function setContainer($key, Container\AbstractContainer $container)
    {
        $key = (string) $key;
        $this->items[$key] = $container;

        return $this;
    }

    /**
     * Retrieve a placeholder container
     *
     * @param  string $key
     * @return Container\AbstractContainer
     */
    public function getContainer($key)
    {
        $key = (string) $key;
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        $container = $this->createContainer($key);

        return $container;
    }

    /**
     * Does a particular container exist?
     *
     * @param  string $key
     * @return bool
     */
    public function containerExists($key)
    {
        $key = (string) $key;

        return array_key_exists($key, $this->items);
    }

    /**
     * createContainer
     *
     * @param  string $key
     * @param  array  $value
     * @return Container\AbstractContainer
     */
    public function createContainer($key, array $value = array())
    {
        $key = (string) $key;

        $this->items[$key] = new $this->containerClass($value);

        return $this->items[$key];
    }

    /**
     * Delete a container
     *
     * @param  string $key
     * @return bool
     */
    public function deleteContainer($key)
    {
        $key = (string) $key;
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
            return true;
        }

        return false;
    }

    /**
     * Set the container class to use
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return Registry
     */
    public function setContainerClass($name)
    {
        if (!class_exists($name)) {
            throw new Exception\DomainException(
                sprintf(
                    '%s expects a valid registry class name; received "%s", which did not resolve',
                    __METHOD__,
                    $name
                )
            );
        }

        if (!in_array('Zend\View\Helper\Placeholder\Container\AbstractContainer', class_parents($name))) {
            throw new Exception\InvalidArgumentException('Invalid Container class specified');
        }

        $this->containerClass = $name;

        return $this;
    }

    /**
     * Retrieve the container class
     *
     * @return string
     */
    public function getContainerClass()
    {
        return $this->containerClass;
    }
}
