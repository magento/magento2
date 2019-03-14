<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Registry model. Used to manage values in registry
 *
 * Registry usage as a shared service introduces temporal, hard to detect coupling into system.
 * It's usage should be avoid. Use service classes or data providers instead.
 *
 * @api
 * @deprecated 102.0.0
 * @since 100.0.2
 */
class Registry
{
    /**
     * Registry collection
     *
     * @var array
     */
    private $_registry = [];

    /**
     * Retrieve a value from registry by a key
     *
     * @param string $key
     * @return mixed
     *
     * @deprecated 102.0.0
     */
    public function registry($key)
    {
        if (isset($this->_registry[$key])) {
            return $this->_registry[$key];
        }
        return null;
    }

    /**
     * Register a new variable
     *
     * @param string $key
     * @param mixed $value
     * @param bool $graceful
     * @return void
     * @throws \RuntimeException
     *
     * @deprecated 102.0.0
     */
    public function register($key, $value, $graceful = false)
    {
        if (isset($this->_registry[$key])) {
            if ($graceful) {
                return;
            }
            throw new \RuntimeException('Registry key "' . $key . '" already exists');
        }
        $this->_registry[$key] = $value;
    }

    /**
     * Unregister a variable from register by key
     *
     * @param string $key
     * @return void
     *
     * @deprecated 102.0.0
     */
    public function unregister($key)
    {
        if (isset($this->_registry[$key])) {
            if (is_object($this->_registry[$key])
                && method_exists($this->_registry[$key], '__destruct')
                && is_callable([$this->_registry[$key], '__destruct'])
            ) {
                $this->_registry[$key]->__destruct();
            }
            unset($this->_registry[$key]);
        }
    }

    /**
     * Destruct registry items
     */
    public function __destruct()
    {
        $keys = array_keys($this->_registry);
        array_walk($keys, [$this, 'unregister']);
    }
}
