<?php
/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/Infrastructure/Instance.php';

/**
 * List of instances
 *
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Infrastructure_InstanceList implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array Array of Zend_Cloud_Infrastructure_Instance
     */
    protected $instances = array();

    /**
     * @var int Iterator key
     */
    protected $iteratorKey = 0;

    /**
     * @var Zend_Cloud_Infrastructure_Adapter
     */
    protected $adapter;

    /**
     * Constructor
     *
     * @param  Adapter $adapter
     * @param  array $instances
     * @return void
     */
    public function __construct($adapter, array $instances = null)
    {
        if (!($adapter instanceof Zend_Cloud_Infrastructure_Adapter)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must pass a Zend_Cloud_Infrastructure_Adapter');
        }
        if (empty($instances)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must pass an array of Instances');
        }

        $this->adapter = $adapter;
        $this->constructFromArray($instances);
    }

    /**
     * Transforms the Array to array of Instances
     *
     * @param  array $list
     * @return void
     */
    protected function constructFromArray(array $list)
    {
        foreach ($list as $instance) {
            $this->addInstance(new Zend_Cloud_Infrastructure_Instance($this->adapter,$instance));
        }
    }

    /**
     * Add an instance
     *
     * @param  Instance
     * @return InstanceList
     */
    protected function addInstance(Zend_Cloud_Infrastructure_Instance $instance)
    {
        $this->instances[] = $instance;
        return $this;
    }

    /**
     * Return number of instances
     *
     * Implement Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->instances);
    }

    /**
     * Return the current element
     *
     * Implement Iterator::current()
     *
     * @return Instance
     */
    public function current()
    {
        return $this->instances[$this->iteratorKey];
    }

    /**
     * Return the key of the current element
     *
     * Implement Iterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->iteratorKey;
    }

    /**
     * Move forward to next element
     *
     * Implement Iterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->iteratorKey++;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * Implement Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->iteratorKey = 0;
    }

    /**
     * Check if there is a current element after calls to rewind() or next()
     *
     * Implement Iterator::valid()
     *
     * @return bool
     */
    public function valid()
    {
        $numItems = $this->count();
        if ($numItems > 0 && $this->iteratorKey < $numItems) {
            return true;
        }
        return false;
    }

    /**
     * Whether the offset exists
     *
     * Implement ArrayAccess::offsetExists()
     *
     * @param  int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ($offset < $this->count());
    }

    /**
     * Return value at given offset
     *
     * Implement ArrayAccess::offsetGet()
     *
     * @param  int $offset
     * @return Instance
     * @throws Zend_Cloud_Infrastructure_Exception
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Illegal index');
        }
        return $this->instances[$offset];
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetSet()
     *
     * @param   int     $offset
     * @param   string  $value
     * @throws  Zend_Cloud_Infrastructure_Exception
     */
    public function offsetSet($offset, $value)
    {
        #require_once 'Zend/Cloud/Infrastructure/Exception.php';
        throw new Zend_Cloud_Infrastructure_Exception('You are trying to set read-only property');
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetUnset()
     *
     * @param   int     $offset
     * @throws  Zend_Cloud_Infrastructure_Exception
     */
    public function offsetUnset($offset)
    {
        #require_once 'Zend/Cloud/Infrastructure/Exception.php';
        throw new Zend_Cloud_Infrastructure_Exception('You are trying to unset read-only property');
    }
}
