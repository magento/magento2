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
 * @package    Zend_Stdlib
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    /**
     * SplPriorityQueue 
     *
     * PHP 5.2.X userland implementation of PHP's SplPriorityQueue
     */
    class SplPriorityQueue implements Iterator , Countable 
    {
        /**
         * Extract data only
         */
        const EXTR_DATA = 0x00000001;

        /**
         * Extract priority only
         */
        const EXTR_PRIORITY = 0x00000002;

        /**
         * Extract an array of ('data' => $value, 'priority' => $priority)
         */
        const EXTR_BOTH = 0x00000003;

        /**
         * Count of items in the queue
         * @var int
         */
        protected $count = 0;

        /**
         * Flag indicating what should be returned when iterating or extracting
         * @var int
         */
        protected $extractFlags = self::EXTR_DATA;

        /**
         * @var bool|array
         */
        protected $preparedQueue = false;

        /**
         * All items in the queue
         * @var array
         */
        protected $queue = array();

        /**
         * Constructor
         * 
         * Creates a new, empty queue
         * 
         * @return void
         */
        public function __construct()
        {
        }

        /**
         * Compare two priorities
         *
         * Returns positive integer if $priority1 is greater than $priority2, 0 
         * if equal, negative otherwise.
         *
         * Unused internally, and only included in order to retain the same 
         * interface as PHP's SplPriorityQueue.
         *
         * @param  mixed $priority1
         * @param  mixed $priority2
         * @return int
         */
        public function compare($priority1, $priority2)
        {
            if ($priority1 > $priority2) {
                return 1;
            }
            if ($priority1 == $priority2) {
                return 0;
            }

            return -1;
        }

        /**
         * Countable: return number of items composed in the queue
         * 
         * @return int
         */
        public function count()
        {
            return $this->count;
        }

        /**
         * Iterator: return current item
         *
         * @return mixed
         */
        public function current()
        {
            if (!$this->preparedQueue) {
                $this->rewind();
            }
            if (!$this->count) {
                throw new OutOfBoundsException('Cannot iterate SplPriorityQueue; no elements present');
            }

if (!is_array($this->preparedQueue)) {
    throw new DomainException(sprintf(
        "Queue was prepared, but is empty?\n    PreparedQueue: %s\n    Internal Queue: %s\n",
        var_export($this->preparedQueue, 1),
        var_export($this->queue, 1)
    ));
}

            $return      = array_shift($this->preparedQueue);
            $priority    = $return['priority'];
            $priorityKey = $return['priorityKey'];
            $key         = $return['key'];
            unset($return['key']);
            unset($return['priorityKey']);
            unset($this->queue[$priorityKey][$key]);

            switch ($this->extractFlags) {
                case self::EXTR_DATA:
                    return $return['data'];
                case self::EXTR_PRIORITY:
                    return $return['priority'];
                case self::EXTR_BOTH:
                default:
                    return $return;
            };
        }

        /**
         * Extract a node from top of the heap and sift up
         *
         * Returns either the value, the priority, or both, depending on the extract flag.
         *
         * @return mixed;
         */
        public function extract()
        {
            if (!$this->count) {
                return null;
            }

            if (!$this->preparedQueue) {
                $this->prepareQueue();
            }

            if (empty($this->preparedQueue)) {
                return null;
            }

            $return      = array_shift($this->preparedQueue);
            $priority    = $return['priority'];
            $priorityKey = $return['priorityKey'];
            $key         = $return['key'];
            unset($return['key']);
            unset($return['priorityKey']);
            unset($this->queue[$priorityKey][$key]);
            $this->count--;

            switch ($this->extractFlags) {
                case self::EXTR_DATA:
                    return $return['data'];
                case self::EXTR_PRIORITY:
                    return $return['priority'];
                case self::EXTR_BOTH:
                default:
                    return $return;
            };
        }

        /**
         * Insert a value into the heap, at the specified priority
         *
         * @param  mixed $value
         * @param  mixed $priority
         * @return void
         */
        public function insert($value, $priority)
        {
            if (!is_scalar($priority)) {
                $priority = serialize($priority);
            }
            if (!isset($this->queue[$priority])) {
                $this->queue[$priority] = array();
            }
            $this->queue[$priority][] = $value;
            $this->count++;
            $this->preparedQueue = false;
        }

        /**
         * Is the queue currently empty?
         *
         * @return bool
         */
        public function isEmpty()
        {
            return (0 == $this->count);
        }

        /**
         * Iterator: return current key
         *
         * @return mixed Usually an int or string
         */
        public function key()
        {
            return $this->count;
        }

        /**
         * Iterator: Move pointer forward
         *
         * @return void
         */
        public function next()
        {
            $this->count--;
        }

        /**
         * Recover from corrupted state and allow further actions on the queue
         *
         * Unimplemented, and only included in order to retain the same interface as PHP's 
         * SplPriorityQueue.
         *
         * @return void
         */
        public function recoverFromCorruption()
        {
        }

        /**
         * Iterator: Move pointer to first item
         *
         * @return void
         */
        public function rewind()
        {
            if (!$this->preparedQueue) {
                $this->prepareQueue();
            }
        }

        /**
         * Set the extract flags
         * 
         * Defines what is extracted by SplPriorityQueue::current(), 
         * SplPriorityQueue::top() and SplPriorityQueue::extract().
         * 
         * - SplPriorityQueue::EXTR_DATA (0x00000001): Extract the data
         * - SplPriorityQueue::EXTR_PRIORITY (0x00000002): Extract the priority
         * - SplPriorityQueue::EXTR_BOTH (0x00000003): Extract an array containing both
         *
         * The default mode is SplPriorityQueue::EXTR_DATA.
         *
         * @param  int $flags
         * @return void
         */
        public function setExtractFlags($flags)
        {
            $expected = array(
                self::EXTR_DATA => true,
                self::EXTR_PRIORITY => true,
                self::EXTR_BOTH => true,
            );
            if (!isset($expected[$flags])) {
                throw new InvalidArgumentException(sprintf('Expected an EXTR_* flag; received %s', $flags));
            }
            $this->extractFlags = $flags;
        }

        /**
         * Return the value or priority (or both) of the top node, depending on 
         * the extract flag
         *
         * @return mixed
         */
        public function top()
        {
            $this->sort();
            $keys = array_keys($this->queue);
            $key  = array_shift($keys);
            if (preg_match('/^(a|O):/', $key)) {
                $key = unserialize($key);
            }

            if ($this->extractFlags == self::EXTR_PRIORITY) {
                return $key;
            }

            if ($this->extractFlags == self::EXTR_DATA) {
                return $this->queue[$key][0];
            }

            return array(
                'data'     => $this->queue[$key][0],
                'priority' => $key,
            );
        }

        /**
         * Iterator: is the current position valid for the queue
         *
         * @return bool
         */
        public function valid()
        {
            return (bool) $this->count;
        }

        /**
         * Sort the queue
         * 
         * @return void
         */
        protected function sort()
        {
            krsort($this->queue);
        }

        /**
         * Prepare the queue for iteration and/or extraction
         * 
         * @return void
         */
        protected function prepareQueue()
        {
            $this->sort();
            $count = $this->count;
            $queue = array();
            foreach ($this->queue as $priority => $values) {
                $priorityKey = $priority;
                if (preg_match('/^(a|O):/', $priority)) {
                    $priority = unserialize($priority);
                }
                foreach ($values as $key => $value) {
                    $queue[$count] = array(
                        'data'        => $value,
                        'priority'    => $priority,
                        'priorityKey' => $priorityKey,
                        'key'         => $key,
                    );
                    $count--;
                }
            }
            $this->preparedQueue = $queue;
        }
    }
}

/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 *
 * @category   Zend
 * @package    Zend_Stdlib
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Stdlib_SplPriorityQueue extends SplPriorityQueue implements Serializable
{
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;

    /**
     * @var bool
     */
    protected $isPhp53;

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->isPhp53 = version_compare(PHP_VERSION, '5.3', '>=');
    }

    /**
     * Insert a value with a given priority
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are 
     * emitted in the same order in which they are inserted.
     * 
     * @param  mixed $datum 
     * @param  mixed $priority 
     * @return void
     */
    public function insert($datum, $priority)
    {
        // If using the native PHP SplPriorityQueue implementation, we need to
        // hack around it to ensure that items registered at the same priority
        // return in the order registered. In the userland version, this is not
        // necessary.
        if ($this->isPhp53) {
            if (!is_array($priority)) {
                $priority = array($priority, $this->serial--);
            }
        }
        parent::insert($datum, $priority);
    }

    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     * 
     * @return array
     */
    public function toArray()
    {
        $this->setExtractFlags(self::EXTR_BOTH);
        $array = array();
        while ($this->valid()) {
            $array[] = $this->current();
            $this->next();
        }
        $this->setExtractFlags(self::EXTR_DATA);

        // Iterating through a priority queue removes items
        foreach ($array as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        // Return only the data
        $return = array();
        foreach ($array as $item) {
            $return[] = $item['data'];
        }

        return $return;
    }

    /**
     * Serialize
     * 
     * @return string
     */
    public function serialize()
    {
        $data = array();
        $this->setExtractFlags(self::EXTR_BOTH);
        while ($this->valid()) {
            $data[] = $this->current();
            $this->next();
        }
        $this->setExtractFlags(self::EXTR_DATA);

        // Iterating through a priority queue removes items
        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        return serialize($data);
    }

    /**
     * Deserialize
     * 
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
}
