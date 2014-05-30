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
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Result.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Represents a single Technorati Search query result object.
 * It is never returned as a standalone object,
 * but it always belongs to a valid Zend_Service_Technorati_SearchResultSet object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @abstract
 */
abstract class Zend_Service_Technorati_Result
{
    /**
     * An associative array of 'fieldName' => 'xmlfieldtag'
     *
     * @var     array
     * @access  protected
     */
    protected $_fields;

    /**
     * The ReST fragment for this result object
     *
     * @var     DomElement
     * @access  protected
     */
    protected $_dom;

    /**
     * Object for $this->_dom
     *
     * @var     DOMXpath
     * @access  protected
     */
    protected $_xpath;


    /**
     * Constructs a new object from DOM Element.
     * Properties are automatically fetched from XML
     * according to array of $_fields to be read.
     *
     * @param   DomElement $result  the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $this->_xpath = new DOMXPath($dom->ownerDocument);
        $this->_dom = $dom;

        // default fields for all search results
        $fields = array();

        // merge with child's object fields
        $this->_fields = array_merge($this->_fields, $fields);

        // add results to appropriate fields
        foreach($this->_fields as $phpName => $xmlName) {
            $query = "./$xmlName/text()";
            $node = $this->_xpath->query($query, $this->_dom);
            if ($node->length == 1) {
                $this->{$phpName} = (string) $node->item(0)->data;
            }
        }
    }

    /**
     * Parses weblog node and sets weblog object.
     *
     * @return  void
     */
    protected function _parseWeblog()
    {
        // weblog object field
        $result = $this->_xpath->query('./weblog', $this->_dom);
        if ($result->length == 1) {
            /**
             * @see Zend_Service_Technorati_Weblog
             */
            #require_once 'Zend/Service/Technorati/Weblog.php';
            $this->_weblog = new Zend_Service_Technorati_Weblog($result->item(0));
        } else {
            $this->_weblog = null;
        }
    }

    /**
     * Returns the document fragment for this object as XML string.
     *
     * @return string   the document fragment for this object
     *                  converted into XML format
     */
    public function getXml()
    {
        return $this->_dom->ownerDocument->saveXML($this->_dom);
    }
}
