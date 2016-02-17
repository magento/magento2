<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\ServiceLocator;

use Zend\Di\Di;
use Zend\Di\Exception;

/**
 * Proxy used to analyze how instances are created by a given Di. Overrides Zend\Di\Di to produce artifacts that
 * represent the process used to instantiate a particular instance
 */
class DependencyInjectorProxy extends Di
{
    /**
     * @var Di
     */
    protected $di;

    /**
     * @param Di $di
     */
    public function __construct(Di $di)
    {
        $this->di              = $di;
        $this->definitions     = $di->definitions();
        $this->instanceManager = $di->instanceManager();
    }

    /**
     * {@inheritDoc}
     * @return GeneratorInstance
     */
    public function get($name, array $params = array())
    {
        return parent::get($name, $params);
    }

    /**
     * {@inheritDoc}
     * @return GeneratorInstance
     */
    public function newInstance($name, array $params = array(), $isShared = true)
    {
        $instance = parent::newInstance($name, $params, $isShared);

        if ($instance instanceof GeneratorInstance) {
            /* @var $instance GeneratorInstance */
            $instance->setShared($isShared);

            // When a callback is used, we don't know instance the class name.
            // That's why we assume $name as the instance alias
            if (null === $instance->getName()) {
                $instance->setAlias($name);
            }
        }

        return $instance;
    }

    /**
     * {@inheritDoc}
     * @return GeneratorInstance
     */
    public function createInstanceViaConstructor($class, $params, $alias = null)
    {
        $callParameters = array();

        if ($this->di->definitions->hasMethod($class, '__construct')
            && (count($this->di->definitions->getMethodParameters($class, '__construct')) > 0)
        ) {
            $callParameters = $this->resolveMethodParameters($class, '__construct', $params, $alias, true, true);
            $callParameters = $callParameters ?: array();
        }

        return new GeneratorInstance($class, $alias, '__construct', $callParameters);
    }

    /**
     * {@inheritDoc}
     * @throws \Zend\Di\Exception\InvalidCallbackException
     * @return GeneratorInstance
     */
    public function createInstanceViaCallback($callback, $params, $alias)
    {
        if (is_string($callback)) {
            $callback = explode('::', $callback);
        }

        if (!is_callable($callback)) {
            throw new Exception\InvalidCallbackException('An invalid constructor callback was provided');
        }

        if (!is_array($callback) || is_object($callback[0])) {
            throw new Exception\InvalidCallbackException(
                'For purposes of service locator generation, constructor callbacks must refer to static methods only'
            );
        }

        $class  = $callback[0];
        $method = $callback[1];

        $callParameters = array();
        if ($this->di->definitions->hasMethod($class, $method)) {
            $callParameters = $this->resolveMethodParameters($class, $method, $params, $alias, true, true);
        }

        $callParameters = $callParameters ?: array();

        return new GeneratorInstance(null, $alias, $callback, $callParameters);
    }

    /**
     * {@inheritDoc}
     */
    public function handleInjectionMethodForObject($class, $method, $params, $alias, $isRequired)
    {
        return array(
            'method' => $method,
            'params' =>  $this->resolveMethodParameters($class, $method, $params, $alias, $isRequired),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function resolveAndCallInjectionMethodForInstance($instance, $method, $params, $alias, $methodIsRequired, $methodClass = null)
    {
        if (!$instance instanceof GeneratorInstance) {
            return parent::resolveAndCallInjectionMethodForInstance($instance, $method, $params, $alias, $methodIsRequired, $methodClass);
        }

        /* @var $instance GeneratorInstance */
        $methodClass = $instance->getClass();
        $callParameters = $this->resolveMethodParameters($methodClass, $method, $params, $alias, $methodIsRequired);

        if ($callParameters !== false) {
            $instance->addMethod(array(
                'method' => $method,
                'params' => $callParameters,
            ));

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function getClass($instance)
    {
        if ($instance instanceof GeneratorInstance) {
            /* @var $instance GeneratorInstance */

            return $instance->getClass();
        }

        return parent::getClass($instance);
    }
}
