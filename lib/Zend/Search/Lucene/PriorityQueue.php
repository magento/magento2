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
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PriorityQueue.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Abstract Priority Queue
 *
 * It implements a priority queue.
 * Please go to "Data Structures and Algorithms",
 * Aho, Hopcroft, and Ullman, Addison-Wesley, 1983 (corrected 1987 edition),
 * for implementation details.
 *
 * It provides O(log(N)) time of put/pop operations, where N is a size of queue
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_PriorityQueue
{
    /**
     * Queue heap
     *
     * Heap contains balanced partial ordered binary tree represented in array
     * [0] - top of the tree
     * [1] - first child of [0]
     * [2] - second child of [0]
     * ...
     * [2*n + 1] - first child of [n]
     * [2*n + 2] - second child of [n]
     *
     * @var array
     */
    private $_heap = array();


    /**
     * Add element to the queue
     *
     * O(log(N)) time
     *
     * @param mixed $element
     */
    public function put($element)
    {
        $nodeId   = count($this->_heap);
        $parentId = ($nodeId-1) >> 1;   // floor( ($nodeId-1)/2 )

        while ($nodeId != 0  &&  $this->_less($element, $this->_heap[$parentId])) {
            // Move parent node down
            $this->_heap[$nodeId] = $this->_heap[$parentId];

            // Move pointer to the next level of tree
            $nodeId   = $parentId;
            $parentId = ($nodeId-1) >> 1;   // floor( ($nodeId-1)/2 )
        }

        // Put new node into the tree
        $this->_heap[$nodeId] = $element;
    }


    /**
     * Return least element of the queue
     *
     * Constant time
     *
     * @return mixed
     */
    public function top()
    {
        if (count($this->_heap) == 0) {
            return null;
        }

        return $this->_heap[0];
    }


    /**
     * Removes and return least element of the queue
     *
     * O(log(N)) time
     *
     * @return mixed
     */
    public function pop()
    {
        if (count($this->_heap) == 0) {
            return null;
        }

        $top = $this->_heap[0];
        $lastId = count($this->_heap) - 1;

        /**
         * Find appropriate position for last node
         */
        $nodeId  = 0;     // Start from a top
        $childId = 1;     // First child

        // Choose smaller child
        if ($lastId > 2  &&  $this->_less($this->_heap[2], $this->_heap[1])) {
            $childId = 2;
        }

        while ($childId < $lastId  &&
               $this->_less($this->_heap[$childId], $this->_heap[$lastId])
          ) {
            // Move child node up
            $this->_heap[$nodeId] = $this->_heap[$childId];

            $nodeId  = $childId;               // Go down
            $childId = ($nodeId << 1) + 1;     // First child

            // Choose smaller child
            if (($childId+1) < $lastId  &&
                $this->_less($this->_heap[$childId+1], $this->_heap[$childId])
               ) {
                $childId++;
            }
        }

        // Move last element to the new position
        $this->_heap[$nodeId] = $this->_heap[$lastId];
        unset($this->_heap[$lastId]);

        return $top;
    }


    /**
     * Clear queue
     */
    public function clear()
    {
        $this->_heap = array();
    }


    /**
     * Compare elements
     *
     * Returns true, if $el1 is less than $el2; else otherwise
     *
     * @param mixed $el1
     * @param mixed $el2
     * @return boolean
     */
    abstract protected function _less($el1, $el2);
}

