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
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_App_Feed
 */
#require_once 'Zend/Gdata/App/Feed.php';

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Extension_OpenSearchTotalResults
 */
#require_once 'Zend/Gdata/Extension/OpenSearchTotalResults.php';

/**
 * @see Zend_Gdata_Extension_OpenSearchStartIndex
 */
#require_once 'Zend/Gdata/Extension/OpenSearchStartIndex.php';

/**
 * @see Zend_Gdata_Extension_OpenSearchItemsPerPage
 */
#require_once 'Zend/Gdata/Extension/OpenSearchItemsPerPage.php';

/**
 * The Gdata flavor of an Atom Feed
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Feed extends Zend_Gdata_App_Feed
{

    /**
     * The classname for individual feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_Entry';

    /**
     * The openSearch:totalResults element
     *
     * @var Zend_Gdata_Extension_OpenSearchTotalResults|null
     */
    protected $_totalResults = null;

    /**
     * The openSearch:startIndex element
     *
     * @var Zend_Gdata_Extension_OpenSearchStartIndex|null
     */
    protected $_startIndex = null;

    /**
     * The openSearch:itemsPerPage element
     *
     * @var Zend_Gdata_Extension_OpenSearchItemsPerPage|null
     */
    protected $_itemsPerPage = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_totalResults != null) {
            $element->appendChild($this->_totalResults->getDOM($element->ownerDocument));
        }
        if ($this->_startIndex != null) {
            $element->appendChild($this->_startIndex->getDOM($element->ownerDocument));
        }
        if ($this->_itemsPerPage != null) {
            $element->appendChild($this->_itemsPerPage->getDOM($element->ownerDocument));
        }

        // ETags are special. We only support them in protocol >= 2.X.
        // This will be duplicated by the HTTP ETag header.
        if ($majorVersion >= 2) {
            if ($this->_etag != null) {
                $element->setAttributeNS($this->lookupNamespace('gd'),
                                         'gd:etag',
                                         $this->_etag);
            }
        }

        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them in the $_entry array based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('openSearch') . ':' . 'totalResults':
            $totalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
            $totalResults->transferFromDOM($child);
            $this->_totalResults = $totalResults;
            break;
        case $this->lookupNamespace('openSearch') . ':' . 'startIndex':
            $startIndex = new Zend_Gdata_Extension_OpenSearchStartIndex();
            $startIndex->transferFromDOM($child);
            $this->_startIndex = $startIndex;
            break;
        case $this->lookupNamespace('openSearch') . ':' . 'itemsPerPage':
            $itemsPerPage = new Zend_Gdata_Extension_OpenSearchItemsPerPage();
            $itemsPerPage->transferFromDOM($child);
            $this->_itemsPerPage = $itemsPerPage;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'etag':
            // ETags are special, since they can be conveyed by either the
            // HTTP ETag header or as an XML attribute.
            $etag = $attribute->nodeValue;
            if ($this->_etag === null) {
                $this->_etag = $etag;
            }
            elseif ($this->_etag != $etag) {
                #require_once('Zend/Gdata/App/IOException.php');
                throw new Zend_Gdata_App_IOException("ETag mismatch");
            }
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
            break;
        }
    }

    /**
     *  Set the value of the totalResults property.
     *
     * @param Zend_Gdata_Extension_OpenSearchTotalResults|null $value The
     *        value of the totalResults property. Use null to unset.
     * @return Zend_Gdata_Feed Provides a fluent interface.
     */
    function setTotalResults($value) {
        $this->_totalResults = $value;
        return $this;
    }

    /**
     * Get the value of the totalResults property.
     *
     * @return Zend_Gdata_Extension_OpenSearchTotalResults|null The value of
     *         the totalResults property, or null if unset.
     */
    function getTotalResults() {
        return $this->_totalResults;
    }

    /**
     * Set the start index property for feed paging.
     *
     * @param Zend_Gdata_Extension_OpenSearchStartIndex|null $value The value
     *        for the startIndex property. Use null to unset.
     * @return Zend_Gdata_Feed Provides a fluent interface.
     */
    function setStartIndex($value) {
        $this->_startIndex = $value;
        return $this;
    }

    /**
     * Get the value of the startIndex property.
     *
     * @return Zend_Gdata_Extension_OpenSearchStartIndex|null The value of the
     *         startIndex property, or null if unset.
     */
    function getStartIndex() {
        return $this->_startIndex;
    }

    /**
     * Set the itemsPerPage property.
     *
     * @param Zend_Gdata_Extension_OpenSearchItemsPerPage|null $value The
     *        value for the itemsPerPage property. Use nul to unset.
     * @return Zend_Gdata_Feed Provides a fluent interface.
     */
    function setItemsPerPage($value) {
        $this->_itemsPerPage = $value;
        return $this;
    }

    /**
     * Get the value of the itemsPerPage property.
     *
     * @return Zend_Gdata_Extension_OpenSearchItemsPerPage|null The value of
     *         the itemsPerPage property, or null if unset.
     */
    function getItemsPerPage() {
        return $this->_itemsPerPage;
    }

}
