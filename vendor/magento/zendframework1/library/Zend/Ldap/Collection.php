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
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Ldap_Collection wraps a list of LDAP entries.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Collection implements Iterator, Countable
{
    /**
     * Iterator
     *
     * @var Zend_Ldap_Collection_Iterator_Default
     */
    protected $_iterator = null;

    /**
     * Current item number
     *
     * @var integer
     */
    protected $_current = -1;

    /**
     * Container for item caching to speed up multiple iterations
     *
     * @var array
     */
    protected $_cache = array();

    /**
     * Constructor.
     *
     * @param Zend_Ldap_Collection_Iterator_Default $iterator
     */
    public function __construct(Zend_Ldap_Collection_Iterator_Default $iterator)
    {
        $this->_iterator = $iterator;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return boolean
     */
    public function close()
    {
        return $this->_iterator->close();
    }

    /**
     * Get all entries as an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this as $item) {
            $data[] = $item;
        }
        return $data;
    }

    /**
     * Get first entry
     *
     * @return array
     */
    public function getFirst()
    {
        if ($this->count() > 0) {
            $this->rewind();
            return $this->current();
        } else {
            return null;
        }
    }

    /**
     * Returns the underlying iterator
     *
     * @return Zend_Ldap_Collection_Iterator_Default
     */
    public function getInnerIterator()
    {
        return $this->_iterator;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return $this->_iterator->count();
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws Zend_Ldap_Exception
     */
    public function current()
    {
        if ($this->count() > 0) {
            if ($this->_current < 0) {
                $this->rewind();
            }
            if (!array_key_exists($this->_current, $this->_cache)) {
                $current = $this->_iterator->current();
                if ($current === null) {
                    return null;
                }
                $this->_cache[$this->_current] = $this->_createEntry($current);
            }
            return $this->_cache[$this->_current];
        } else {
            return null;
        }
    }

    /**
     * Creates the data structure for the given entry data
     *
     * @param  array $data
     * @return array
     */
    protected function _createEntry(array $data)
    {
        return $data;
    }

    /**
     * Return the current result item DN
     *
     * @return string|null
     */
    public function dn()
    {
        if ($this->count() > 0) {
            if ($this->_current < 0) {
                $this->rewind();
            }
            return $this->_iterator->key();
        } else {
            return null;
        }
    }

    /**
     * Return the current result item key
     * Implements Iterator
     *
     * @return int|null
     */
    public function key()
    {
        if ($this->count() > 0) {
            if ($this->_current < 0) {
                $this->rewind();
            }
            return $this->_current;
        } else {
            return null;
        }
    }

    /**
     * Move forward to next result item
     * Implements Iterator
     *
     * @throws Zend_Ldap_Exception
     */
    public function next()
    {
        $this->_iterator->next();
        $this->_current++;
    }

    /**
     * Rewind the Iterator to the first result item
     * Implements Iterator
     *
     * @throws Zend_Ldap_Exception
     */
    public function rewind()
    {
        $this->_iterator->rewind();
        $this->_current = 0;
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return boolean
     */
    public function valid()
    {
        if (isset($this->_cache[$this->_current])) {
            return true;
        } else {
            return $this->_iterator->valid();
        }
    }
}
