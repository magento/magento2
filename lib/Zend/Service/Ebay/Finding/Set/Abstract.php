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
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Ebay_Finding_Set_Abstract implements SeekableIterator, Countable
{
    /**
     * @var DOMNodeList
     */
    protected $_nodes;

    /**
     * @var integer
     */
    protected $_key = 0;

    /**
     * @param  DOMNodeList $nodes
     * @return void
     */
    public function __construct(DOMNodeList $nodes)
    {
        $this->_nodes = $nodes;
        $this->_init();
    }

    /**
     * Initialize object.
     *
     * Called from {@link __construct()} as final step of object initialization.
     *
     * @return void
     */
    protected function _init()
    {
    }

    /**
     * Implement SeekableIterator::seek()
     *
     * @param  integer $key
     * @throws OutOfBoundsException When $key is not seekable
     * @return void
     */
    public function seek($key)
    {
        if ($key < 0 || $key >= $this->count()) {
            $message = "Position '{$key}' is not seekable.";
            throw new OutOfBoundsException($message);
        }
        $this->_key = $key;
    }

    /**
     * Implement Iterator::key()
     *
     * @return integer
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Implement Iterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->_key++;
    }

    /**
     * Implement Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->_key = 0;
    }

    /**
     * Implement Iterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_key >= 0 && $this->_key < $this->count();
    }

    /**
     * Implement Countable::current()
     *
     * @return integer
     */
    public function count()
    {
        return $this->_nodes ? $this->_nodes->length : 0;
    }
}
