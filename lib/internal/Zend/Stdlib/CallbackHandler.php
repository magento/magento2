<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Stdlib
 */

namespace Zend\Stdlib;

use Closure;
use ReflectionClass;
use WeakRef;

/**
 * CallbackHandler
 *
 * A handler for a event, event, filterchain, etc. Abstracts PHP callbacks,
 * primarily to allow for lazy-loading and ensuring availability of default
 * arguments (currying).
 *
 * @category   Zend
 * @package    Zend_Stdlib
 */
class CallbackHandler
{
    /**
     * @var string|array|callable PHP callback to invoke
     */
    protected $callback;

    /**
     * Callback metadata, if any
     * @var array
     */
    protected $metadata;

    /**
     * PHP version is greater as 5.4rc1?
     * @var boolean
     */
    protected static $isPhp54;

    /**
     * Is pecl/weakref extension installed?
     * @var boolean
     */
    protected static $hasWeakRefExtension;

    /**
     * Constructor
     *
     * @param  string|array|object|callable $callback PHP callback
     * @param  array                        $metadata  Callback metadata
     */
    public function __construct($callback, array $metadata = array())
    {
        $this->metadata  = $metadata;
        $this->registerCallback($callback);
    }

    /**
     * Registers the callback provided in the constructor
     *
     * If you have pecl/weakref {@see http://pecl.php.net/weakref} installed,
     * this method provides additional behavior.
     *
     * If a callback is a functor, or an array callback composing an object
     * instance, this method will pass the object to a WeakRef instance prior
     * to registering the callback.
     *
     * @param  callable $callback
     * @throws Exception\InvalidCallbackException
     * @return void
     */
    protected function registerCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception\InvalidCallbackException('Invalid callback provided; not callable');
        }

        if (null === self::$hasWeakRefExtension) {
            self::$hasWeakRefExtension = class_exists('WeakRef');
        }

        // If pecl/weakref is not installed, simply store the callback and return
        if (!self::$hasWeakRefExtension) {
            $this->callback = $callback;
            return;
        }

        // If WeakRef exists, we want to use it.

        // If we have a non-closure object, pass it to WeakRef, and then
        // register it.
        if (is_object($callback) && !$callback instanceof Closure) {
            $this->callback = new WeakRef($callback);
            return;
        }

        // If we have a string or closure, register as-is
        if (!is_array($callback)) {
            $this->callback = $callback;
            return;
        }

        list($target, $method) = $callback;

        // If we have an array callback, and the first argument is not an
        // object, register as-is
        if (!is_object($target)) {
            $this->callback = $callback;
            return;
        }

        // We have an array callback with an object as the first argument;
        // pass it to WeakRef, and then register the new callback
        $target = new WeakRef($target);
        $this->callback = array($target, $method);
    }

    /**
     * Retrieve registered callback
     *
     * @return callable
     */
    public function getCallback()
    {
        $callback = $this->callback;

        // String callbacks -- simply return
        if (is_string($callback)) {
            return $callback;
        }

        // WeakRef callbacks -- pull it out of the object and return it
        if ($callback instanceof WeakRef) {
            return $callback->get();
        }

        // Non-WeakRef object callback -- return it
        if (is_object($callback)) {
            return $callback;
        }

        // Array callback with WeakRef object -- retrieve the object first, and
        // then return
        list($target, $method) = $callback;
        if ($target instanceof WeakRef) {
            return array($target->get(), $method);
        }

        // Otherwise, return it
        return $callback;
    }

    /**
     * Invoke handler
     *
     * @param  array $args Arguments to pass to callback
     * @return mixed
     */
    public function call(array $args = array())
    {
        $callback = $this->getCallback();

        // WeakRef object will return null if the real object was disposed
        if (null === $callback) {
            return null;
        }

        // Minor performance tweak, if the callback gets called more than once
        if (!isset(self::$isPhp54)) {
            self::$isPhp54 = version_compare(PHP_VERSION, '5.4.0rc1', '>=');
        }

        $argCount = count($args);

        if (self::$isPhp54 && is_string($callback)) {
            $result = $this->validateStringCallbackFor54($callback);

            if ($result !== true && $argCount <= 3) {
                $callback       = $result;
                // Minor performance tweak, if the callback gets called more
                // than once
                $this->callback = $result;
            }
        }

        // Minor performance tweak; use call_user_func() until > 3 arguments
        // reached
        switch ($argCount) {
            case 0:
                if (self::$isPhp54) {
                    return $callback();
                }
                return call_user_func($callback);
            case 1:
                if (self::$isPhp54) {
                    return $callback(array_shift($args));
                }
                return call_user_func($callback, array_shift($args));
            case 2:
                $arg1 = array_shift($args);
                $arg2 = array_shift($args);
                if (self::$isPhp54) {
                    return $callback($arg1, $arg2);
                }
                return call_user_func($callback, $arg1, $arg2);
            case 3:
                $arg1 = array_shift($args);
                $arg2 = array_shift($args);
                $arg3 = array_shift($args);
                if (self::$isPhp54) {
                    return $callback($arg1, $arg2, $arg3);
                }
                return call_user_func($callback, $arg1, $arg2, $arg3);
            default:
                return call_user_func_array($callback, $args);
        }
    }

    /**
     * Invoke as functor
     *
     * @return mixed
     */
    public function __invoke()
    {
        return $this->call(func_get_args());
    }

    /**
     * Get all callback metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Retrieve a single metadatum
     *
     * @param  string $name
     * @return mixed
     */
    public function getMetadatum($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        return null;
    }

    /**
     * Validate a static method call
     *
     * Validates that a static method call in PHP 5.4 will actually work
     *
     * @param  string $callback
     * @return true|array
     * @throws Exception\InvalidCallbackException if invalid
     */
    protected function validateStringCallbackFor54($callback)
    {
        if (!strstr($callback, '::')) {
            return true;
        }

        list($class, $method) = explode('::', $callback, 2);

        if (!class_exists($class)) {
            throw new Exception\InvalidCallbackException(sprintf(
                'Static method call "%s" refers to a class that does not exist',
                $callback
            ));
        }

        $r = new ReflectionClass($class);
        if (!$r->hasMethod($method)) {
            throw new Exception\InvalidCallbackException(sprintf(
                'Static method call "%s" refers to a method that does not exist',
                $callback
            ));
        }
        $m = $r->getMethod($method);
        if (!$m->isStatic()) {
            throw new Exception\InvalidCallbackException(sprintf(
                'Static method call "%s" refers to a method that is not static',
                $callback
            ));
        }

        // returning a non boolean value may not be nice for a validate method,
        // but that allows the usage of a static string callback without using
        // the call_user_func function.
        return array($class, $method);
    }
}
