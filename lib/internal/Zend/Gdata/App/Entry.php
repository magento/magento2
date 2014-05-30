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
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_FeedEntryParent
 */
#require_once 'Zend/Gdata/App/FeedEntryParent.php';

/**
 * @see Zend_Gdata_App_Extension_Content
 */
#require_once 'Zend/Gdata/App/Extension/Content.php';

/**
 * @see Zend_Gdata_App_Extension_Edited
 */
#require_once 'Zend/Gdata/App/Extension/Edited.php';

/**
 * @see Zend_Gdata_App_Extension_Published
 */
#require_once 'Zend/Gdata/App/Extension/Published.php';

/**
 * @see Zend_Gdata_App_Extension_Source
 */
#require_once 'Zend/Gdata/App/Extension/Source.php';

/**
 * @see Zend_Gdata_App_Extension_Summary
 */
#require_once 'Zend/Gdata/App/Extension/Summary.php';

/**
 * @see Zend_Gdata_App_Extension_Control
 */
#require_once 'Zend/Gdata/App/Extension/Control.php';

/**
 * Concrete class for working with Atom entries.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_Entry extends Zend_Gdata_App_FeedEntryParent
{

    /**
     * Root XML element for Atom entries.
     *
     * @var string
     */
    protected $_rootElement = 'entry';

    /**
     * Class name for each entry in this feed*
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_App_Entry';

    /**
     * atom:content element
     *
     * @var Zend_Gdata_App_Extension_Content
     */
    protected $_content = null;

    /**
     * atom:published element
     *
     * @var Zend_Gdata_App_Extension_Published
     */
    protected $_published = null;

    /**
     * atom:source element
     *
     * @var Zend_Gdata_App_Extension_Source
     */
    protected $_source = null;

    /**
     * atom:summary element
     *
     * @var Zend_Gdata_App_Extension_Summary
     */
    protected $_summary = null;

    /**
     * app:control element
     *
     * @var Zend_Gdata_App_Extension_Control
     */
    protected $_control = null;

    /**
     * app:edited element
     *
     * @var Zend_Gdata_App_Extension_Edited
     */
    protected $_edited = null;

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_content != null) {
            $element->appendChild($this->_content->getDOM($element->ownerDocument));
        }
        if ($this->_published != null) {
            $element->appendChild($this->_published->getDOM($element->ownerDocument));
        }
        if ($this->_source != null) {
            $element->appendChild($this->_source->getDOM($element->ownerDocument));
        }
        if ($this->_summary != null) {
            $element->appendChild($this->_summary->getDOM($element->ownerDocument));
        }
        if ($this->_control != null) {
            $element->appendChild($this->_control->getDOM($element->ownerDocument));
        }
        if ($this->_edited != null) {
            $element->appendChild($this->_edited->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('atom') . ':' . 'content':
            $content = new Zend_Gdata_App_Extension_Content();
            $content->transferFromDOM($child);
            $this->_content = $content;
            break;
        case $this->lookupNamespace('atom') . ':' . 'published':
            $published = new Zend_Gdata_App_Extension_Published();
            $published->transferFromDOM($child);
            $this->_published = $published;
            break;
        case $this->lookupNamespace('atom') . ':' . 'source':
            $source = new Zend_Gdata_App_Extension_Source();
            $source->transferFromDOM($child);
            $this->_source = $source;
            break;
        case $this->lookupNamespace('atom') . ':' . 'summary':
            $summary = new Zend_Gdata_App_Extension_Summary();
            $summary->transferFromDOM($child);
            $this->_summary = $summary;
            break;
        case $this->lookupNamespace('app') . ':' . 'control':
            $control = new Zend_Gdata_App_Extension_Control();
            $control->transferFromDOM($child);
            $this->_control = $control;
            break;
        case $this->lookupNamespace('app') . ':' . 'edited':
            $edited = new Zend_Gdata_App_Extension_Edited();
            $edited->transferFromDOM($child);
            $this->_edited = $edited;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Uploads changes in this entry to the server using Zend_Gdata_App
     *
     * @param string|null $uri The URI to send requests to, or null if $data
     *        contains the URI.
     * @param string|null $className The name of the class that should we
     *        deserializing the server response. If null, then
     *        'Zend_Gdata_App_Entry' will be used.
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return Zend_Gdata_App_Entry The updated entry.
     * @throws Zend_Gdata_App_Exception
     */
    public function save($uri = null, $className = null, $extraHeaders = array())
    {
        return $this->getService()->updateEntry($this,
                                                $uri,
                                                $className,
                                                $extraHeaders);
    }

    /**
     * Deletes this entry to the server using the referenced
     * Zend_Http_Client to do a HTTP DELETE to the edit link stored in this
     * entry's link collection.
     *
     * @return void
     * @throws Zend_Gdata_App_Exception
     */
    public function delete()
    {
        $this->getService()->delete($this);
    }

    /**
     * Reload the current entry. Returns a new copy of the entry as returned
     * by the server, or null if no changes exist. This does not
     * modify the current entry instance.
     *
     * @param string|null The URI to send requests to, or null if $data
     *        contains the URI.
     * @param string|null The name of the class that should we deserializing
     *        the server response. If null, then 'Zend_Gdata_App_Entry' will
     *        be used.
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return mixed A new instance of the current entry with updated data, or
     *         null if the server reports that no changes have been made.
     * @throws Zend_Gdata_App_Exception
     */
    public function reload($uri = null, $className = null, $extraHeaders = array())
    {
        // Get URI
        $editLink = $this->getEditLink();
        if (($uri === null) && $editLink != null) {
            $uri = $editLink->getHref();
        }

        // Set classname to current class, if not otherwise set
        if ($className === null) {
            $className = get_class($this);
        }

        // Append ETag, if present (Gdata v2 and above, only) and doesn't
        // conflict with existing headers
        if ($this->_etag != null
                && !array_key_exists('If-Match', $extraHeaders)
                && !array_key_exists('If-None-Match', $extraHeaders)) {
            $extraHeaders['If-None-Match'] = $this->_etag;
        }

        // If an HTTP 304 status (Not Modified)is returned, then we return
        // null.
        $result = null;
        try {
            $result = $this->service->importUrl($uri, $className, $extraHeaders);
        } catch (Zend_Gdata_App_HttpException $e) {
            if ($e->getResponse()->getStatus() != '304')
                throw $e;
        }

        return $result;
    }

    /**
     * Gets the value of the atom:content element
     *
     * @return Zend_Gdata_App_Extension_Content
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Sets the value of the atom:content element
     *
     * @param Zend_Gdata_App_Extension_Content $value
     * @return Zend_Gdata_App_Entry Provides a fluent interface
     */
    public function setContent($value)
    {
        $this->_content = $value;
        return $this;
    }

    /**
     * Sets the value of the atom:published element
     * This represents the publishing date for an entry
     *
     * @return Zend_Gdata_App_Extension_Published
     */
    public function getPublished()
    {
        return $this->_published;
    }

    /**
     * Sets the value of the atom:published element
     * This represents the publishing date for an entry
     *
     * @param Zend_Gdata_App_Extension_Published $value
     * @return Zend_Gdata_App_Entry Provides a fluent interface
     */
    public function setPublished($value)
    {
        $this->_published = $value;
        return $this;
    }

    /**
     * Gets the value of the atom:source element
     *
     * @return Zend_Gdata_App_Extension_Source
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Sets the value of the atom:source element
     *
     * @param Zend_Gdata_App_Extension_Source $value
     * @return Zend_Gdata_App_Entry Provides a fluent interface
     */
    public function setSource($value)
    {
        $this->_source = $value;
        return $this;
    }

    /**
     * Gets the value of the atom:summary element
     * This represents a textual summary of this entry's content
     *
     * @return Zend_Gdata_App_Extension_Summary
     */
    public function getSummary()
    {
        return $this->_summary;
    }

    /**
     * Sets the value of the atom:summary element
     * This represents a textual summary of this entry's content
     *
     * @param Zend_Gdata_App_Extension_Summary $value
     * @return Zend_Gdata_App_Entry Provides a fluent interface
     */
    public function setSummary($value)
    {
        $this->_summary = $value;
        return $this;
    }

    /**
     * Gets the value of the app:control element
     *
     * @return Zend_Gdata_App_Extension_Control
     */
    public function getControl()
    {
        return $this->_control;
    }

    /**
     * Sets the value of the app:control element
     *
     * @param Zend_Gdata_App_Extension_Control $value
     * @return Zend_Gdata_App_Entry Provides a fluent interface
     */
    public function setControl($value)
    {
        $this->_control = $value;
        return $this;
    }

}
