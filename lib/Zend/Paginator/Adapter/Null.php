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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Null.php 21150 2010-02-23 16:27:36Z matthew $
 */

/**
 * @see Zend_Paginator_Adapter_Interface
 */
#require_once 'Zend/Paginator/Adapter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Paginator_Adapter_Null implements Zend_Paginator_Adapter_Interface
{
    /**
     * Item count
     *
     * @var integer
     */
    protected $_count = null;

    /**
     * Constructor.
     *
     * @param array $count Total item count
     */
    public function __construct($count = 0)
    {
        $this->_count = $count;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($offset >= $this->count()) {
            return array();
        }

        $remainItemCount  = $this->count() - $offset;
        $currentItemCount = $remainItemCount > $itemCountPerPage ? $itemCountPerPage : $remainItemCount;

        return array_fill(0, $currentItemCount, null);
    }

    /**
     * Returns the total number of rows in the array.
     *
     * @return integer
     */
    public function count()
    {
        return $this->_count;
    }
}