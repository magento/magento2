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
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Service_Amazon_Item
 */
#require_once 'Zend/Service/Amazon/Item.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_ResultSet implements SeekableIterator
{
    /**
     * A DOMNodeList of <Item> elements
     *
     * @var DOMNodeList
     */
    protected $_results = null;

    /**
     * Amazon Web Service Return Document
     *
     * @var DOMDocument
     */
    protected $_dom;

    /**
     * XPath Object for $this->_dom
     *
     * @var DOMXPath
     */
    protected $_xpath;

    /**
     * Current index for SeekableIterator
     *
     * @var int
     */
    protected $_currentIndex = 0;

    /**
     * Create an instance of Zend_Service_Amazon_ResultSet and create the necessary data objects
     *
     * @param  DOMDocument $dom
     * @return void
     */
    public function __construct(DOMDocument $dom)
    {
        $this->_dom = $dom;
        $this->_xpath = new DOMXPath($dom);
        $this->_xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $this->_results = $this->_xpath->query('//az:Item');
    }

    /**
     * Total Number of results returned
     *
     * @return int Total number of results returned
     */
    public function totalResults()
    {
        $result = $this->_xpath->query('//az:TotalResults/text()');
        return (int) $result->item(0)->data;
    }

    /**
     * Total Number of pages returned
     *
     * @return int Total number of pages returned
     */
    public function totalPages()
    {
        $result = $this->_xpath->query('//az:TotalPages/text()');
        return (int) $result->item(0)->data;
    }

    /**
     * Implement SeekableIterator::current()
     *
     * @return Zend_Service_Amazon_Item
     */
    public function current()
    {
        return new Zend_Service_Amazon_Item($this->_results->item($this->_currentIndex));
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
     * @throws OutOfBoundsException
     * @return void
     */
    public function seek($index)
    {
        $indexInt = (int) $index;
        if ($indexInt >= 0 && (null === $this->_results || $indexInt < $this->_results->length)) {
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
        return null !== $this->_results && $this->_currentIndex < $this->_results->length;
    }
}
