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
 * @package    Zend_Dom
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Results for DOM XPath query
 *
 * @package    Zend_Dom
 * @subpackage Query
 * @uses       Iterator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dom_Query_Result implements Iterator,Countable
{
    /**
     * Number of results
     * @var int
     */
    protected $_count;

    /**
     * CSS Selector query
     * @var string
     */
    protected $_cssQuery;

    /**
     * @var DOMDocument
     */
    protected $_document;

    /**
     * @var DOMNodeList
     */
    protected $_nodeList;

    /**
     * Current iterator position
     * @var int
     */
    protected $_position = 0;

    /**
     * @var DOMXPath
     */
    protected $_xpath;

    /**
     * XPath query
     * @var string
     */
    protected $_xpathQuery;

    /**
     * Constructor
     *
     * @param  string $cssQuery
     * @param  string|array $xpathQuery
     * @param  DOMDocument $document
     * @param  DOMNodeList $nodeList
     */
    public function  __construct($cssQuery, $xpathQuery, DOMDocument $document, DOMNodeList $nodeList)
    {
        $this->_cssQuery   = $cssQuery;
        $this->_xpathQuery = $xpathQuery;
        $this->_document   = $document;
        $this->_nodeList   = $nodeList;
    }

    /**
     * Retrieve CSS Query
     *
     * @return string
     */
    public function getCssQuery()
    {
        return $this->_cssQuery;
    }

    /**
     * Retrieve XPath query
     *
     * @return string
     */
    public function getXpathQuery()
    {
        return $this->_xpathQuery;
    }

    /**
     * Retrieve DOMDocument
     *
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * Iterator: rewind to first element
     *
     * @return DOMNode|null
     */
    public function rewind()
    {
        $this->_position = 0;
        return $this->_nodeList->item(0);
    }

    /**
     * Iterator: is current position valid?
     *
     * @return bool
     */
    public function valid()
    {
        if (in_array($this->_position, range(0, $this->_nodeList->length - 1)) && $this->_nodeList->length > 0) {
            return true;
        }
        return false;
    }

    /**
     * Iterator: return current element
     *
     * @return DOMElement
     */
    public function current()
    {
        return $this->_nodeList->item($this->_position);
    }

    /**
     * Iterator: return key of current element
     *
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Iterator: move to next element
     *
     * @return DOMNode|null
     */
    public function next()
    {
        ++$this->_position;
        return $this->_nodeList->item($this->_position);
    }

    /**
     * Countable: get count
     *
     * @return int
     */
    public function count()
    {
        return $this->_nodeList->length;
    }
}
