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
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: UserFeed.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Photos
 */
#require_once 'Zend/Gdata/Photos.php';

/**
 * @see Zend_Gdata_Feed
 */
#require_once 'Zend/Gdata/Feed.php';

/**
 * @see Zend_Gdata_Photos_UserEntry
 */
#require_once 'Zend/Gdata/Photos/UserEntry.php';

/**
 * @see Zend_Gdata_Photos_AlbumEntry
 */
#require_once 'Zend/Gdata/Photos/AlbumEntry.php';

/**
 * @see Zend_Gdata_Photos_PhotoEntry
 */
#require_once 'Zend/Gdata/Photos/PhotoEntry.php';

/**
 * @see Zend_Gdata_Photos_TagEntry
 */
#require_once 'Zend/Gdata/Photos/TagEntry.php';

/**
 * @see Zend_Gdata_Photos_CommentEntry
 */
#require_once 'Zend/Gdata/Photos/CommentEntry.php';

/**
 * Data model for a collection of entries for a specific user, usually
 * provided by the servers.
 *
 * For information on requesting this feed from a server, see the
 * service class, Zend_Gdata_Photos.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_UserFeed extends Zend_Gdata_Feed
{

    /**
     * gphoto:user element
     *
     * @var Zend_Gdata_Photos_Extension_User
     */
    protected $_gphotoUser = null;

    /**
     * gphoto:thumbnail element
     *
     * @var Zend_Gdata_Photos_Extension_Thumbnail
     */
    protected $_gphotoThumbnail = null;

    /**
     * gphoto:nickname element
     *
     * @var Zend_Gdata_Photos_Extension_Nickname
     */
    protected $_gphotoNickname = null;

    protected $_entryClassName = 'Zend_Gdata_Photos_UserEntry';
    protected $_feedClassName = 'Zend_Gdata_Photos_UserFeed';

    protected $_entryKindClassMapping = array(
        'http://schemas.google.com/photos/2007#album' => 'Zend_Gdata_Photos_AlbumEntry',
        'http://schemas.google.com/photos/2007#photo' => 'Zend_Gdata_Photos_PhotoEntry',
        'http://schemas.google.com/photos/2007#comment' => 'Zend_Gdata_Photos_CommentEntry',
        'http://schemas.google.com/photos/2007#tag' => 'Zend_Gdata_Photos_TagEntry'
    );

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);
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
            case $this->lookupNamespace('gphoto') . ':' . 'user';
                $user = new Zend_Gdata_Photos_Extension_User();
                $user->transferFromDOM($child);
                $this->_gphotoUser = $user;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'nickname';
                $nickname = new Zend_Gdata_Photos_Extension_Nickname();
                $nickname->transferFromDOM($child);
                $this->_gphotoNickname = $nickname;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'thumbnail';
                $thumbnail = new Zend_Gdata_Photos_Extension_Thumbnail();
                $thumbnail->transferFromDOM($child);
                $this->_gphotoThumbnail = $thumbnail;
                break;
            case $this->lookupNamespace('atom') . ':' . 'entry':
                $entryClassName = $this->_entryClassName;
                $tmpEntry = new Zend_Gdata_App_Entry($child);
                $categories = $tmpEntry->getCategory();
                foreach ($categories as $category) {
                    if ($category->scheme == Zend_Gdata_Photos::KIND_PATH &&
                        $this->_entryKindClassMapping[$category->term] != "") {
                            $entryClassName = $this->_entryKindClassMapping[$category->term];
                            break;
                    } else {
                        #require_once 'Zend/Gdata/App/Exception.php';
                        throw new Zend_Gdata_App_Exception('Entry is missing kind declaration.');
                    }
                }

                $newEntry = new $entryClassName($child);
                $newEntry->setHttpClient($this->getHttpClient());
                $this->_entry[] = $newEntry;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoUser != null) {
            $element->appendChild($this->_gphotoUser->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoNickname != null) {
            $element->appendChild($this->_gphotoNickname->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoThumbnail != null) {
            $element->appendChild($this->_gphotoThumbnail->getDOM($element->ownerDocument));
        }

        return $element;
    }

    /**
     * Get the value for this element's gphoto:user attribute.
     *
     * @see setGphotoUser
     * @return string The requested attribute.
     */
    public function getGphotoUser()
    {
        return $this->_gphotoUser;
    }

    /**
     * Set the value for this element's gphoto:user attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_User The element being modified.
     */
    public function setGphotoUser($value)
    {
        $this->_gphotoUser = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:nickname attribute.
     *
     * @see setGphotoNickname
     * @return string The requested attribute.
     */
    public function getGphotoNickname()
    {
        return $this->_gphotoNickname;
    }

    /**
     * Set the value for this element's gphoto:nickname attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Nickname The element being modified.
     */
    public function setGphotoNickname($value)
    {
        $this->_gphotoNickname = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:thumbnail attribute.
     *
     * @see setGphotoThumbnail
     * @return string The requested attribute.
     */
    public function getGphotoThumbnail()
    {
        return $this->_gphotoThumbnail;
    }

    /**
     * Set the value for this element's gphoto:thumbnail attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Thumbnail The element being modified.
     */
    public function setGphotoThumbnail($value)
    {
        $this->_gphotoThumbnail = $value;
        return $this;
    }

}
