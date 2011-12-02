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
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ResultSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_ResultSet implements SeekableIterator
{
    /**
     * Total number of results available
     *
     * @var int
     */
    public $totalResultsAvailable;

    /**
     * The number of results in this result set
     *
     * @var int
     */
    public $totalResultsReturned;

    /**
     * The offset in the total result set of this search set
     *
     * @var int
     */
    public $firstResultPosition;

    /**
     * A DOMNodeList of results
     *
     * @var DOMNodeList
     */
    protected $_results;

    /**
     * Yahoo Web Service Return Document
     *
     * @var DOMDocument
     */
    protected $_dom;

    /**
     * Xpath Object for $this->_dom
     *
     * @var DOMXPath
     */
    protected $_xpath;

    /**
     * Current Index for SeekableIterator
     *
     * @var int
     */
    protected $_currentIndex = 0;


    /**
     * Parse the search response and retrieve the results for iteration
     *
     * @param  DOMDocument $dom the REST fragment for this object
     * @return void
     */
    public function __construct(DOMDocument $dom)
    {
        $this->totalResultsAvailable = (int) $dom->documentElement->getAttribute('totalResultsAvailable');
        $this->totalResultsReturned = (int) $dom->documentElement->getAttribute('totalResultsReturned');
        $this->firstResultPosition = (int) $dom->documentElement->getAttribute('firstResultPosition');

        $this->_dom = $dom;
        $this->_xpath = new DOMXPath($dom);

        $this->_xpath->registerNamespace('yh', $this->_namespace);

        $this->_results = $this->_xpath->query('//yh:Result');
    }


    /**
     * Total Number of results returned
     *
     * @return int Total number of results returned
     */
    public function totalResults()
    {
        return $this->totalResultsReturned;
    }


    /**
     * Implement SeekableIterator::current()
     *
     * Must be implemented by child classes
     *
     * @throws Zend_Service_Exception
     * @return Zend_Service_Yahoo_Result
     */
    public function current()
    {
        /**
         * @see Zend_Service_Exception
         */
        #require_once 'Zend/Service/Exception.php';
        throw new Zend_Service_Exception('Zend_Service_Yahoo_ResultSet::current() must be implemented by child '
                                       . 'classes');
    }


    /**
     * Implement SeekableIterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->_currentIndex;
    }


    /**
     * Implement SeekableIterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->_currentIndex += 1;
    }


    /**
     * Implement SeekableIterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->_currentIndex = 0;
    }


    /**
     * Implement SeekableIterator::seek()
     *
     * @param  int $index
     * @return void
     * @throws OutOfBoundsException
     */
    public function seek($index)
    {
        $indexInt = (int) $index;
        if ($indexInt >= 0 && $indexInt < $this->_results->length) {
            $this->_currentIndex = $indexInt;
        } else {
            throw new OutOfBoundsException("Illegal index '$index'");
        }
    }


    /**
     * Implement SeekableIterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_currentIndex < $this->_results->length;
    }
}
