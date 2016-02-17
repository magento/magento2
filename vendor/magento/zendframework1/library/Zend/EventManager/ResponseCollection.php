<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    class SplStack implements Iterator, ArrayAccess, Countable
    {
        /**
         * Delete items during iteration
         */
        const IT_MODE_DELETE = 1;

        /**
         * Keep items during iteration
         */
        const IT_MODE_KEEP = 0;

        /**
         * Mode used when iterating
         * @var int
         */
        protected $mode = self::IT_MODE_KEEP;

        /**
         * Count of elements in the stack 
         * 
         * @var int
         */
        protected $count = 0;

        /**
         * Data represented by this stack
         * 
         * @var array
         */
        protected $data = array();

        /**
         * Sorted stack of values
         * 
         * @var false|array
         */
        protected $stack = false;

        /**
         * Set the iterator mode
         *
         * Must be set to one of IT_MODE_DELETE or IT_MODE_KEEP
         * 
         * @todo   Currently, IteratorMode is ignored, as we use the default (keep); should this be implemented?
         * @param  int $mode 
         * @return void
         * @throws InvalidArgumentException
         */
        public function setIteratorMode($mode)
        {
            $expected = array(
                self::IT_MODE_DELETE => true,
                self::IT_MODE_KEEP => true,
            );

            if (!isset($expected[$mode])) {
                throw new InvalidArgumentException(sprintf('Invalid iterator mode specified ("%s")', $mode));
            }

            $this->mode = $mode;
        }

        /**
         * Return last element in the stack
         * 
         * @return mixed
         */
        public function bottom()
        {
            $this->rewind();
            $value = array_pop($this->stack);
            array_push($this->stack, $value);
            return $value;
        }

        /**
         * Countable: return count of items in the stack
         * 
         * @return int
         */
        public function count()
        {
            return $this->count;
        }

        /**
         * Iterator: return current item in the stack
         * 
         * @return mixed
         */
        public function current()
        {
            if (!$this->stack) {
                $this->rewind();
            }
            return current($this->stack);
        }

        /**
         * Get iteration mode
         * 
         * @return int
         */
        public function getIteratorMode()
        {
            return $this->mode;
        }

        /**
         * Is the stack empty?
         *
         * @return bool
         */
        public function isEmpty()
        {
            return ($this->count === 0);
        }

        /**
         * Iterator: return key of current item in the stack
         *
         * @return mixed
         */
        public function key()
        {
            if (!$this->stack) {
                $this->rewind();
            }
            return key($this->stack);
        }

        /**
         * Iterator: advance pointer to next item in the stack
         * 
         * @return void
         */
        public function next()
        {
            if (!$this->stack) {
                $this->rewind();
            }
            return next($this->stack);
        }

        /**
         * ArrayAccess: does an item exist at the specified offset?
         * 
         * @param  mixed $index 
         * @return bool
         */
        public function offsetExists($index)
        {
            return array_key_exists($index, $this->data);
        }

        /**
         * ArrayAccess: get the item at the specified offset
         * 
         * @param  mixed $index 
         * @return mixed
         * @throws OutOfRangeException
         */
        public function offsetGet($index)
        {
            if (!$this->offsetExists($index)) {
                throw OutOfRangeException(sprintf('Invalid index ("%s") specified', $index));
            }
            return $this->data[$index];
        }

        /**
         * ArrayAccess: add an item at the specified offset
         * 
         * @param  mixed $index 
         * @param  mixed $newval 
         * @return void
         */
        public function offsetSet($index, $newval)
        {
            $this->data[$index] = $newval;
            $this->stack = false;
            $this->count++;
        }

        /**
         * ArrayAccess: unset the item at the specified offset
         * 
         * @param  mixed $index 
         * @return void
         * @throws OutOfRangeException
         */
        public function offsetUnset($index)
        {
            if (!$this->offsetExists($index)) {
                throw OutOfRangeException(sprintf('Invalid index ("%s") specified', $index));
            }
            unset($this->data[$index]);
            $this->stack = false;
            $this->count--;
        }

        /**
         * Pop a node from the end of the stack
         *
         * @return mixed
         * @throws RuntimeException
         */
        public function pop()
        {
            $val         = array_pop($this->data);
            $this->stack = false;
            $this->count--;
            return $val;
        }

        /**
         * Move the iterator to the previous node
         *
         * @todo   Does this need to be implemented?
         * @return void
         */
        public function prev()
        {
        }

        /**
         * Push an element to the list
         * 
         * @param  mixed $value 
         * @return void
         */
        public function push($value)
        {
            array_push($this->data, $value);
            $this->count++;
            $this->stack  = false;
        }

        /**
         * Iterator: rewind to beginning of stack
         * 
         * @return void
         */
        public function rewind()
        {
            if (is_array($this->stack)) {
                return reset($this->stack);
            }
            $this->stack = array_reverse($this->data, true);
        }

        /**
         * Serialize the storage
         *
         * @return string
         */
        public function serialize()
        {
            return serialize($this->data);
        }

        /**
         * Shifts a node from the beginning of the list
         *
         * @return mixed
         * @throws RuntimeException
         */
        public function shift()
        {
            $val         = array_shift($this->data);
            $this->stack = false;
            $this->count--;
            return $val;
        }

        /**
         * Peek at the top node of the stack
         * 
         * @return mixed
         */
        public function top()
        {
            $this->rewind();
            $value = array_shift($this->stack);
            array_unshift($this->stack, $value);
            return $value;
        }

        /**
         * Unserialize the storage
         *
         * @param  string
         * @return void
         */
        public function unserialize($serialized)
        {
            $this->data  = unserialize($serialized);
            $this->stack = false;
        }

        /**
         * Unshift a node onto the beginning of the list
         *
         * @param  mixed $value
         * @return void
         */
        public function unshift($value)
        {
            array_unshift($this->data, $value);
            $this->count++;
            $this->stack  = false;
        }
        
        /**
         * Iterator: is the current pointer valid?
         *
         * @return bool
         */
        public function valid()
        {
            $key = key($this->stack);
            $var = ($key !== null && $key !== false);
            return $var;
        }
    }
}

/**
 * Collection of signal handler return values
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_EventManager_ResponseCollection extends SplStack 
{
    protected $stopped = false;

    /**
     * Did the last response provided trigger a short circuit of the stack?
     * 
     * @return bool
     */
    public function stopped()
    {
        return $this->stopped;
    }

    /**
     * Mark the collection as stopped (or its opposite)
     * 
     * @param  bool $flag 
     * @return Zend_EventManager_ResponseCollection
     */
    public function setStopped($flag)
    {
        $this->stopped = (bool) $flag;
        return $this;
    }

    /**
     * Convenient access to the first handler return value.
     *
     * @return mixed The first handler return value
     */
    public function first()
    {
        return parent::bottom();
    }

    /**
     * Convenient access to the last handler return value.
     *
     * If the collection is empty, returns null. Otherwise, returns value
     * returned by last handler.
     *
     * @return mixed The last handler return value
     */
    public function last()
    {
        if (count($this) === 0) {
            return null;
        }
        return parent::top();
    }

    /**
     * Check if any of the responses match the given value.
     *
     * @param  mixed $value The value to look for among responses
     */
    public function contains($value)
    {
        foreach ($this as $response) {
            if ($response === $value) {
                return true;
            }
        }
        return false;
    }
}
