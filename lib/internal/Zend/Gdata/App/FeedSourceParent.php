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
 * @version    $Id: FeedSourceParent.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_Entry
 */
#require_once 'Zend/Gdata/App/Entry.php';

/**
 * @see Zend_Gdata_App_FeedSourceParent
 */
#require_once 'Zend/Gdata/App/FeedEntryParent.php';

/**
 * @see Zend_Gdata_App_Extension_Generator
 */
#require_once 'Zend/Gdata/App/Extension/Generator.php';

/**
 * @see Zend_Gdata_App_Extension_Icon
 */
#require_once 'Zend/Gdata/App/Extension/Icon.php';

/**
 * @see Zend_Gdata_App_Extension_Logo
 */
#require_once 'Zend/Gdata/App/Extension/Logo.php';

/**
 * @see Zend_Gdata_App_Extension_Subtitle
 */
#require_once 'Zend/Gdata/App/Extension/Subtitle.php';

/**
 * Atom feed class
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Gdata_App_FeedSourceParent extends Zend_Gdata_App_FeedEntryParent
{

    /**
     * The classname for individual feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_App_Entry';

    /**
     * Root XML element for Atom entries.
     *
     * @var string
     */
    protected $_rootElement = null;

    protected $_generator = null;
    protected $_icon = null;
    protected $_logo = null;
    protected $_subtitle = null;

    /**
     * Set the HTTP client instance
     *
     * Sets the HTTP client object to use for retrieving the feed.
     *
     * @deprecated Deprecated as of Zend Framework 1.7. Use
     *             setService() instead.
     * @param  Zend_Http_Client $httpClient
     * @return Zend_Gdata_App_FeedSourceParent Provides a fluent interface
     */
    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        parent::setHttpClient($httpClient);
        foreach ($this->_entry as $entry) {
            $entry->setHttpClient($httpClient);
        }
        return $this;
    }

    /**
     * Set the active service instance for this feed and all enclosed entries.
     * This will be used to perform network requests, such as when calling
     * save() and delete().
     *
     * @param Zend_Gdata_App $instance The new service instance.
     * @return Zend_Gdata_App_FeedEntryParent Provides a fluent interface.
     */
    public function setService($instance)
    {
        parent::setService($instance);
        foreach ($this->_entry as $entry) {
            $entry->setService($instance);
        }
        return $this;
    }

    /**
     * Make accessing some individual elements of the feed easier.
     *
     * Special accessors 'entry' and 'entries' are provided so that if
     * you wish to iterate over an Atom feed's entries, you can do so
     * using foreach ($feed->entries as $entry) or foreach
     * ($feed->entry as $entry).
     *
     * @param  string $var The property to access.
     * @return mixed
     */
    public function __get($var)
    {
        switch ($var) {
            default:
                return parent::__get($var);
        }
    }


    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_generator != null) {
            $element->appendChild($this->_generator->getDOM($element->ownerDocument));
        }
        if ($this->_icon != null) {
            $element->appendChild($this->_icon->getDOM($element->ownerDocument));
        }
        if ($this->_logo != null) {
            $element->appendChild($this->_logo->getDOM($element->ownerDocument));
        }
        if ($this->_subtitle != null) {
            $element->appendChild($this->_subtitle->getDOM($element->ownerDocument));
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
        case $this->lookupNamespace('atom') . ':' . 'generator':
            $generator = new Zend_Gdata_App_Extension_Generator();
            $generator->transferFromDOM($child);
            $this->_generator = $generator;
            break;
        case $this->lookupNamespace('atom') . ':' . 'icon':
            $icon = new Zend_Gdata_App_Extension_Icon();
            $icon->transferFromDOM($child);
            $this->_icon = $icon;
            break;
        case $this->lookupNamespace('atom') . ':' . 'logo':
            $logo = new Zend_Gdata_App_Extension_Logo();
            $logo->transferFromDOM($child);
            $this->_logo = $logo;
            break;
        case $this->lookupNamespace('atom') . ':' . 'subtitle':
            $subtitle = new Zend_Gdata_App_Extension_Subtitle();
            $subtitle->transferFromDOM($child);
            $this->_subtitle = $subtitle;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * @return Zend_Gdata_AppExtension_Generator
     */
    public function getGenerator()
    {
        return $this->_generator;
    }

    /**
     * @param Zend_Gdata_App_Extension_Generator $value
     * @return Zend_Gdata_App_FeedSourceParent Provides a fluent interface
     */
    public function setGenerator($value)
    {
        $this->_generator = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_AppExtension_Icon
     */
    public function getIcon()
    {
        return $this->_icon;
    }

    /**
     * @param Zend_Gdata_App_Extension_Icon $value
     * @return Zend_Gdata_App_FeedSourceParent Provides a fluent interface
     */
    public function setIcon($value)
    {
        $this->_icon = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_AppExtension_logo
     */
    public function getlogo()
    {
        return $this->_logo;
    }

    /**
     * @param Zend_Gdata_App_Extension_logo $value
     * @return Zend_Gdata_App_FeedSourceParent Provides a fluent interface
     */
    public function setlogo($value)
    {
        $this->_logo = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_AppExtension_Subtitle
     */
    public function getSubtitle()
    {
        return $this->_subtitle;
    }

    /**
     * @param Zend_Gdata_App_Extension_Subtitle $value
     * @return Zend_Gdata_App_FeedSourceParent Provides a fluent interface
     */
    public function setSubtitle($value)
    {
        $this->_subtitle = $value;
        return $this;
    }

}
