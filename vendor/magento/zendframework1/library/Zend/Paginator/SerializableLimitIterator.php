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
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Paginator_SerializableLimitIterator extends LimitIterator implements Serializable, ArrayAccess
{

    /**
     * Offset to first element
     *
     * @var int
     */
    private $_offset;

    /**
     * Maximum number of elements to show or -1 for all
     *
     * @var int
     */
    private $_count;

    /**
     * Construct a Zend_Paginator_SerializableLimitIterator
     *
     * @param Iterator $it Iterator to limit (must be serializable by un-/serialize)
     * @param int $offset Offset to first element
     * @param int $count Maximum number of elements to show or -1 for all
     * @see LimitIterator::__construct
     */
    public function __construct (Iterator $it, $offset=0, $count=-1)
    {
        parent::__construct($it, $offset, $count);
        $this->_offset = $offset;
        $this->_count = $count;
    }

    /**
     * @return string representation of the instance
     */
    public function serialize()
    {
        return serialize(array(
            'it'     => $this->getInnerIterator(),
            'offset' => $this->_offset,
            'count'  => $this->_count,
            'pos'    => $this->getPosition(),
        ));
    }

    /**
     * @param string $data representation of the instance
     */
    public function unserialize($data)
    {
        $dataArr = unserialize($data);
        $this->__construct($dataArr['it'], $dataArr['offset'], $dataArr['count']);
        $this->seek($dataArr['pos']+$dataArr['offset']);
    }

    /**
     * Returns value of the Iterator
     *
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $currentOffset = $this->key();
        $this->seek($offset);
        $current = $this->current();
        $this->seek($currentOffset);
        return $current;
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Determine if a value of Iterator is set and is not NULL
     *
     * @param int $offset
     */
    public function offsetExists($offset)
    {
        if ($offset > 0 && $offset < $this->_count) {
            try {
                $currentOffset = $this->key();
                $this->seek($offset);
                $current = $this->current();
                $this->seek($currentOffset);
                return null !== $current;
            } catch (OutOfBoundsException $e) {
                // reset position in case of exception is assigned null
                $this->rewind();
                $this->seek($currentOffset);
                return false;
            }
        }
        return false;
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
    }
}
