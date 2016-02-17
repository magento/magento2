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
 * @subpackage Node
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Ldap_Node
 */
#require_once 'Zend/Ldap/Node.php';

/**
 * Zend_Ldap_Node_ChildrenIterator provides an iterator to a collection of children nodes.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_ChildrenIterator implements Iterator, Countable, RecursiveIterator, ArrayAccess
{
    /**
     * An array of Zend_Ldap_Node objects
     *
     * @var array
     */
    private $_data;

    /**
     * Constructor.
     *
     * @param  array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Returns the number of child nodes.
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }

    /**
     * Return the current child.
     * Implements Iterator
     *
     * @return Zend_Ldap_Node
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Return the child'd RDN.
     * Implements Iterator
     *
     * @return string
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Move forward to next child.
     * Implements Iterator
     */
    public function next()
    {
        next($this->_data);
    }

    /**
     * Rewind the Iterator to the first child.
     * Implements Iterator
     */
    public function rewind()
    {
        reset($this->_data);
    }

    /**
     * Check if there is a current child
     * after calls to rewind() or next().
     * Implements Iterator
     *
     * @return boolean
     */
    public function valid()
    {
        return (current($this->_data)!==false);
    }

    /**
     * Checks if current node has children.
     * Returns whether the current element has children.
     *
     * @return boolean
     */
    public function hasChildren()
    {
        if ($this->current() instanceof Zend_Ldap_Node) {
            return $this->current()->hasChildren();
        } else {
            return false;
        }
    }

    /**
     * Returns the children for the current node.
     *
     * @return Zend_Ldap_Node_ChildrenIterator
     */
    public function getChildren()
    {
        if ($this->current() instanceof Zend_Ldap_Node) {
            return $this->current()->getChildren();
        } else {
            return null;
        }
    }

    /**
     * Returns a child with a given RDN.
     * Implements ArrayAccess.
     *
     * @param  string $rdn
     * @return Zend_Ldap_node
     */
    public function offsetGet($rdn)
    {
        if ($this->offsetExists($rdn)) {
            return $this->_data[$rdn];
        } else {
            return null;
        }
    }

    /**
     * Checks whether a given rdn exists.
     * Implements ArrayAccess.
     *
     * @param  string $rdn
     * @return boolean
     */
    public function offsetExists($rdn)
    {
        return (array_key_exists($rdn, $this->_data));
    }

    /**
     * Does nothing.
     * Implements ArrayAccess.
     *
     * @param  string $name
     * @return null
     */
    public function offsetUnset($name) { }

    /**
     * Does nothing.
     * Implements ArrayAccess.
     *
     * @param  string $name
     * @param  mixed $value
     * @return null
     */
    public function offsetSet($name, $value) { }

    /**
     * Get all children as an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this as $rdn => $node) {
            $data[$rdn] = $node;
        }
        return $data;
    }
}
