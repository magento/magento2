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
 * @package    Zend_Service_Rackspace
 * @subpackage Files
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Rackspace/Files/Object.php';
#require_once 'Zend/Service/Rackspace/Files.php';

/**
 * List of servers retrived from the GoGrid web service
 *
 * @uses       ArrayAccess
 * @uses       Countable
 * @uses       Iterator
 * @uses       OutOfBoundsException
 * @uses       Zend_Service_Rackspace_Files
 * @category   Zend
 * @package    Zend_Service_Rackspace
 * @subpackage Files
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Rackspace_Files_ObjectList implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array of Zend_Service_Rackspace_Files_Object
     */
    protected $objects = array();
    /**
     * @var int Iterator key
     */
    protected $iteratorKey = 0;
    /**
     * @var RackspaceFiles
     */
    protected $service;
    /**
     * The container name of the object list
     *
     * @var string
     */
    protected $container;
    /**
     * Construct
     *
     * @param  array $list
     * @return boolean
     */
    public function __construct($service,$list,$container)
    {
        if (!($service instanceof Zend_Service_Rackspace_Files)) {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception("You must pass a Zend_Service_Rackspace_Files object");
        }
        if (!is_array($list)) {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception("You must pass an array of data objects");
        }
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception("You must pass the container of the object list");
        }
        $this->service= $service;
        $this->container= $container;
        $this->_constructFromArray($list);
    }
    /**
     * Transforms the Array to array of container
     *
     * @param  array $list
     * @return void
     */
    private function _constructFromArray(array $list)
    {
        foreach ($list as $obj) {
            $obj['container']= $this->container;
            $this->_addObject(new Zend_Service_Rackspace_Files_Object($this->service,$obj));
        }
    }
    /**
     * Add an object
     *
     * @param  Zend_Service_Rackspace_Files_Object $obj
     * @return Zend_Service_Rackspace_Files_ObjectList
     */
    protected function _addObject (Zend_Service_Rackspace_Files_Object $obj)
    {
        $this->objects[] = $obj;
        return $this;
    }
    /**
     * Return number of servers
     *
     * Implement Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->objects);
    }
    /**
     * Return the current element
     *
     * Implement Iterator::current()
     *
     * @return Zend_Service_Rackspace_Files_Object
     */
    public function current()
    {
        return $this->objects[$this->iteratorKey];
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
        $this->iteratorKey += 1;
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
        } else {
            return false;
        }
    }
    /**
     * Whether the offset exists
     *
     * Implement ArrayAccess::offsetExists()
     *
     * @param   int     $offset
     * @return  bool
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
     * @param   int     $offset
     * @throws  Zend_Service_Rackspace_Files_Exception
     * @return  Zend_Service_Rackspace_Files_Object
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->objects[$offset];
        } else {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception('Illegal index');
        }
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetSet()
     *
     * @param   int     $offset
     * @param   string  $value
     * @throws  Zend_Service_Rackspace_Files_Exception
     */
    public function offsetSet($offset, $value)
    {
        #require_once 'Zend/Service/Rackspace/Files/Exception.php';
        throw new Zend_Service_Rackspace_Files_Exception('You are trying to set read-only property');
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetUnset()
     *
     * @param   int     $offset
     * @throws  Zend_Service_Rackspace_Files_Exception
     */
    public function offsetUnset($offset)
    {
        #require_once 'Zend/Service/Rackspace/Files/Exception.php';
        throw new Zend_Service_Rackspace_Files_Exception('You are trying to unset read-only property');
    }
}
