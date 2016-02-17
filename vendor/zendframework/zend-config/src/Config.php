<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Provides a property based interface to an array.
 * The data are read-only unless $allowModifications is set to true
 * on construction.
 *
 * Implements Countable, Iterator and ArrayAccess
 * to facilitate easy access to the data.
 */
class Config implements Countable, Iterator, ArrayAccess
{
    /**
     * Whether modifications to configuration data are allowed.
     *
     * @var bool
     */
    protected $allowModifications;

    /**
     * Data within the configuration.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Used when unsetting values during iteration to ensure we do not skip
     * the next element.
     *
     * @var bool
     */
    protected $skipNextIteration;

    /**
     * Constructor.
     *
     * Data is read-only unless $allowModifications is set to true
     * on construction.
     *
     * @param  array   $array
     * @param  bool $allowModifications
     */
    public function __construct(array $array, $allowModifications = false)
    {
        $this->allowModifications = (bool) $allowModifications;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new static($value, $this->allowModifications);
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set a value in the config.
     *
     * Only allow setting of a property if $allowModifications  was set to true
     * on construction. Otherwise, throw an exception.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws Exception\RuntimeException
     */
    public function __set($name, $value)
    {
        if ($this->allowModifications) {
            if (is_array($value)) {
                $value = new static($value, true);
            }

            if (null === $name) {
                $this->data[] = $value;
            } else {
                $this->data[$name] = $value;
            }
        } else {
            throw new Exception\RuntimeException('Config is read only');
        }
    }

    /**
     * Deep clone of this instance to ensure that nested Zend\Configs are also
     * cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $array = array();

        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = clone $value;
            } else {
                $array[$key] = $value;
            }
        }

        $this->data = $array;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $data  = $this->data;

        /** @var self $value */
        foreach ($data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * isset() overloading
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function __unset($name)
    {
        if (!$this->allowModifications) {
            throw new Exception\InvalidArgumentException('Config is read only');
        } elseif (isset($this->data[$name])) {
            unset($this->data[$name]);
            $this->skipNextIteration = true;
        }
    }

    /**
     * count(): defined by Countable interface.
     *
     * @see    Countable::count()
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * current(): defined by Iterator interface.
     *
     * @see    Iterator::current()
     * @return mixed
     */
    public function current()
    {
        $this->skipNextIteration = false;
        return current($this->data);
    }

    /**
     * key(): defined by Iterator interface.
     *
     * @see    Iterator::key()
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * next(): defined by Iterator interface.
     *
     * @see    Iterator::next()
     * @return void
     */
    public function next()
    {
        if ($this->skipNextIteration) {
            $this->skipNextIteration = false;
            return;
        }

        next($this->data);
    }

    /**
     * rewind(): defined by Iterator interface.
     *
     * @see    Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $this->skipNextIteration = false;
        reset($this->data);
    }

    /**
     * valid(): defined by Iterator interface.
     *
     * @see    Iterator::valid()
     * @return bool
     */
    public function valid()
    {
        return ($this->key() !== null);
    }

    /**
     * offsetExists(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetExists()
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * offsetGet(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetGet()
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * offsetSet(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetSet()
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * offsetUnset(): defined by ArrayAccess interface.
     *
     * @see    ArrayAccess::offsetUnset()
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Merge another Config with this one.
     *
     * For duplicate keys, the following will be performed:
     * - Nested Configs will be recursively merged.
     * - Items in $merge with INTEGER keys will be appended.
     * - Items in $merge with STRING keys will overwrite current values.
     *
     * @param  Config $merge
     * @return Config
     */
    public function merge(Config $merge)
    {
        /** @var Config $value */
        foreach ($merge as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                if (is_int($key)) {
                    $this->data[] = $value;
                } elseif ($value instanceof self && $this->data[$key] instanceof self) {
                    $this->data[$key]->merge($value);
                } else {
                    if ($value instanceof self) {
                        $this->data[$key] = new static($value->toArray(), $this->allowModifications);
                    } else {
                        $this->data[$key] = $value;
                    }
                }
            } else {
                if ($value instanceof self) {
                    $this->data[$key] = new static($value->toArray(), $this->allowModifications);
                } else {
                    $this->data[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Prevent any more modifications being made to this instance.
     *
     * Useful after merge() has been used to merge multiple Config objects
     * into one object which should then not be modified again.
     *
     * @return void
     */
    public function setReadOnly()
    {
        $this->allowModifications = false;

        /** @var Config $value */
        foreach ($this->data as $value) {
            if ($value instanceof self) {
                $value->setReadOnly();
            }
        }
    }

    /**
     * Returns whether this Config object is read only or not.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return !$this->allowModifications;
    }
}
