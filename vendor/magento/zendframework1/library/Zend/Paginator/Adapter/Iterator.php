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
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Paginator_Adapter_Interface
 */
#require_once 'Zend/Paginator/Adapter/Interface.php';

/**
 * @see Zend_Paginator_SerializableLimitIterator
 */
#require_once 'Zend/Paginator/SerializableLimitIterator.php';

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Paginator_Adapter_Iterator implements Zend_Paginator_Adapter_Interface
{
    /**
     * Iterator which implements Countable
     *
     * @var Iterator
     */
    protected $_iterator = null;

    /**
     * Item count
     *
     * @var integer
     */
    protected $_count = null;

    /**
     * Constructor.
     *
     * @param  Iterator $iterator Iterator to paginate
     * @throws Zend_Paginator_Exception
     */
    public function __construct(Iterator $iterator)
    {
        if (!$iterator instanceof Countable) {
            /**
             * @see Zend_Paginator_Exception
             */
            #require_once 'Zend/Paginator/Exception.php';

            throw new Zend_Paginator_Exception('Iterator must implement Countable');
        }

        $this->_iterator = $iterator;
        $this->_count = count($iterator);
    }

    /**
     * Returns an iterator of items for a page, or an empty array.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return LimitIterator|array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->_count == 0) {
            return array();
        }

        // @link http://bugs.php.net/bug.php?id=49906 | ZF-8084
        // return new LimitIterator($this->_iterator, $offset, $itemCountPerPage);
        return new Zend_Paginator_SerializableLimitIterator($this->_iterator, $offset, $itemCountPerPage);
    }

    /**
     * Returns the total number of rows in the collection.
     *
     * @return integer
     */
    public function count()
    {
        return $this->_count;
    }
}
