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
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: InboxEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Media_Entry
 */
#require_once 'Zend/Gdata/Media/Entry.php';

/**
 * @see Zend_Gdata_Extension_Rating
 */
#require_once 'Zend/Gdata/Extension/Rating.php';

/**
 * @see Zend_Gdata_Extension_Comments
 */
#require_once 'Zend/Gdata/Extension/Comments.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Statistics
 */
#require_once 'Zend/Gdata/YouTube/Extension/Statistics.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Description
 */
#require_once 'Zend/Gdata/YouTube/Extension/Description.php';


/**
 * Represents the YouTube message flavor of an Atom entry
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_InboxEntry extends Zend_Gdata_Media_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_InboxEntry';

    /**
     * The gd:comments element of this entry.
     *
     * @var Zend_Gdata_Extension_Comments
     */
    protected $_comments = null;

    /**
     * The gd:rating element of this entry.
     *
     * @var Zend_Gdata_Extension_Rating
     */
    protected $_rating = null;

    /**
     * The yt:statistics element of this entry.
     *
     * @var Zend_Gdata_YouTube_Extension_Statistics
     */
    protected $_statistics = null;

    /**
     * The yt:description element of this entry.
     *
     * @var Zend_Gdata_YouTube_Extension_Description
     */
    protected $_description = null;

    /**
     * Creates a subscription entry, representing an individual subscription
     * in a list of subscriptions, usually associated with an individual user.
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_description != null) {
            $element->appendChild(
                $this->_description->getDOM($element->ownerDocument));
        }
        if ($this->_rating != null) {
            $element->appendChild(
                $this->_rating->getDOM($element->ownerDocument));
        }
        if ($this->_statistics != null) {
            $element->appendChild(
                $this->_statistics->getDOM($element->ownerDocument));
        }
        if ($this->_comments != null) {
            $element->appendChild(
                $this->_comments->getDOM($element->ownerDocument));
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
            case $this->lookupNamespace('gd') . ':' . 'comments':
                $comments = new Zend_Gdata_Extension_Comments();
                $comments->transferFromDOM($child);
                $this->_comments = $comments;
                break;
            case $this->lookupNamespace('gd') . ':' . 'rating':
                $rating = new Zend_Gdata_Extension_Rating();
                $rating->transferFromDOM($child);
                $this->_rating = $rating;
                break;
            case $this->lookupNamespace('yt') . ':' . 'description':
                $description = new Zend_Gdata_YouTube_Extension_Description();
                $description->transferFromDOM($child);
                $this->_description = $description;
                break;
            case $this->lookupNamespace('yt') . ':' . 'statistics':
                $statistics = new Zend_Gdata_YouTube_Extension_Statistics();
                $statistics->transferFromDOM($child);
                $this->_statistics = $statistics;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    /**
     * Get the yt:description
     *
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_Extension_Description|null
     */
    public function getDescription()
    {
        if ($this->getMajorProtocolVersion() == 2) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getDescription ' .
                ' method is only supported in version 1 of the YouTube ' .
                'API.');
        } else {
            return $this->_description;
        }
    }

    /**
     * Sets the yt:description element for a new inbox entry.
     *
     * @param Zend_Gdata_YouTube_Extension_Description $description The
     *        description.
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_InboxEntry Provides a fluent interface
     */
    public function setDescription($description = null)
    {
        if ($this->getMajorProtocolVersion() == 2) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setDescription ' .
                ' method is only supported in version 1 of the YouTube ' .
                'API.');
        } else {
            $this->_description = $description;
            return $this;
        }
    }

    /**
     * Get the gd:rating element for the inbox entry
     *
     * @return Zend_Gdata_Extension_Rating|null
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * Sets the gd:rating element for the inbox entry
     *
     * @param Zend_Gdata_Extension_Rating $rating The rating for the video in
     *        the message
     * @return Zend_Gdata_YouTube_InboxEntry Provides a fluent interface
     */
    public function setRating($rating = null)
    {
        $this->_rating = $rating;
        return $this;
    }

    /**
     * Get the gd:comments element of the inbox entry.
     *
     * @return Zend_Gdata_Extension_Comments|null
     */
    public function getComments()
    {
        return $this->_comments;
    }

    /**
     * Sets the gd:comments element for the inbox entry
     *
     * @param Zend_Gdata_Extension_Comments $comments The comments feed link
     * @return Zend_Gdata_YouTube_InboxEntry Provides a fluent interface
     */
    public function setComments($comments = null)
    {
        $this->_comments = $comments;
        return $this;
    }

    /**
     * Get the yt:statistics element for the inbox entry
     *
     * @return Zend_Gdata_YouTube_Extension_Statistics|null
     */
    public function getStatistics()
    {
        return $this->_statistics;
    }

    /**
     * Sets the yt:statistics element for the inbox entry
     *
     * @param Zend_Gdata_YouTube_Extension_Statistics $statistics The
     *        statistics element for the video in the message
     * @return Zend_Gdata_YouTube_InboxEntry Provides a fluent interface
     */
    public function setStatistics($statistics = null)
    {
        $this->_statistics = $statistics;
        return $this;
    }


}
