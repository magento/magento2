<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Server\Reflection;

use ReflectionClass as PhpReflectionClass;

/**
 * Class/Object reflection
 *
 * Proxies calls to a ReflectionClass object, and decorates getMethods() by
 * creating its own list of {@link Zend\Server\Reflection\ReflectionMethod}s.
 */
class ReflectionClass
{
    /**
     * Optional configuration parameters; accessible via {@link __get} and
     * {@link __set()}
     * @var array
     */
    protected $config = array();

    /**
     * Array of {@link \Zend\Server\Reflection\Method}s
     * @var array
     */
    protected $methods = array();

    /**
     * Namespace
     * @var string
     */
    protected $namespace = null;

    /**
     * ReflectionClass object
     * @var PhpReflectionClass
     */
    protected $reflection;

    /**
     * Constructor
     *
     * Create array of dispatchable methods, each a
     * {@link Zend\Server\Reflection\ReflectionMethod}. Sets reflection object property.
     *
     * @param PhpReflectionClass $reflection
     * @param string $namespace
     * @param mixed $argv
     */
    public function __construct(PhpReflectionClass $reflection, $namespace = null, $argv = false)
    {
        $this->reflection = $reflection;
        $this->setNamespace($namespace);

        foreach ($reflection->getMethods() as $method) {
            // Don't aggregate magic methods
            if ('__' == substr($method->getName(), 0, 2)) {
                continue;
            }

            if ($method->isPublic()) {
                // Get signatures and description
                $this->methods[] = new ReflectionMethod($this, $method, $this->getNamespace(), $argv);
            }
        }
    }

    /**
     * Proxy reflection calls
     *
     * @param string $method
     * @param array $args
     * @throws Exception\BadMethodCallException
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->reflection, $method)) {
            return call_user_func_array(array($this->reflection, $method), $args);
        }

        throw new Exception\BadMethodCallException('Invalid reflection method');
    }

    /**
     * Retrieve configuration parameters
     *
     * Values are retrieved by key from {@link $config}. Returns null if no
     * value found.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return;
    }

    /**
     * Set configuration parameters
     *
     * Values are stored by $key in {@link $config}.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Return array of dispatchable {@link \Zend\Server\Reflection\ReflectionMethod}s.
     *
     * @access public
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get namespace for this class
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set namespace for this class
     *
     * @param string $namespace
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function setNamespace($namespace)
    {
        if (empty($namespace)) {
            $this->namespace = '';
            return;
        }

        if (!is_string($namespace) || !preg_match('/[a-z0-9_\.]+/i', $namespace)) {
            throw new Exception\InvalidArgumentException('Invalid namespace');
        }

        $this->namespace = $namespace;
    }

    /**
     * Wakeup from serialization
     *
     * Reflection needs explicit instantiation to work correctly. Re-instantiate
     * reflection object on wakeup.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->reflection = new PhpReflectionClass($this->getName());
    }
}
