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
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Gapps_Extension_Login
 */
#require_once 'Zend/Gdata/Gapps/Extension/Login.php';

/**
 * @see Zend_Gdata_Gapps_Extension_Nickname
 */
#require_once 'Zend/Gdata/Gapps/Extension/Nickname.php';

/**
 * Data model class for a Google Apps Nickname Entry.
 *
 * Each nickname entry describes a single nickname within a Google Apps
 * hosted domain. Each user may own several nicknames, but each nickname may
 * only belong to one user. Multiple entries are contained within instances
 * of Zend_Gdata_Gapps_NicknameFeed.
 *
 * To transfer nickname entries to and from the Google Apps servers,
 * including creating new entries, refer to the Google Apps service class,
 * Zend_Gdata_Gapps.
 *
 * This class represents <atom:entry> in the Google Data protocol.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gapps_NicknameEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Gapps_NicknameEntry';

    /**
     * <apps:login> element used to hold information about the owner
     * of this nickname, including their username.
     *
     * @var Zend_Gdata_Gapps_Extension_Login
     */
    protected $_login = null;

    /**
     * <apps:nickname> element used to hold the name of this nickname.
     *
     * @var Zend_Gdata_Gapps_Extension_Nickname
     */
    protected $_nickname = null;

    /**
     * Create a new instance.
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct($element);
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_login !== null) {
            $element->appendChild($this->_login->getDOM($element->ownerDocument));
        }
        if ($this->_nickname !== null) {
            $element->appendChild($this->_nickname->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('apps') . ':' . 'login';
                $login = new Zend_Gdata_Gapps_Extension_Login();
                $login->transferFromDOM($child);
                $this->_login = $login;
                break;
            case $this->lookupNamespace('apps') . ':' . 'nickname';
                $nickname = new Zend_Gdata_Gapps_Extension_Nickname();
                $nickname->transferFromDOM($child);
                $this->_nickname = $nickname;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    /**
     * Get the value of the login property for this object.
     *
     * @see setLogin
     * @return Zend_Gdata_Gapps_Extension_Login The requested object.
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Set the value of the login property for this object. This property
     * is used to store the username address of the current user.
     *
     * @param Zend_Gdata_Gapps_Extension_Login $value The desired value for
     *          this instance's login property.
     * @return Zend_Gdata_Gapps_NicknameEntry Provides a fluent interface.
     */
    public function setLogin($value)
    {
        $this->_login = $value;
        return $this;
    }

    /**
     * Get the value of the nickname property for this object.
     *
     * @see setNickname
     * @return Zend_Gdata_Gapps_Extension_Nickname The requested object.
     */
    public function getNickname()
    {
        return $this->_nickname;
    }

    /**
     * Set the value of the nickname property for this object. This property
     * is used to store the the name of the current nickname.
     *
     * @param Zend_Gdata_Gapps_Extension_Nickname $value The desired value for
     *          this instance's nickname property.
     * @return Zend_Gdata_Gapps_NicknameEntry Provides a fluent interface.
     */
    public function setNickname($value)
    {
        $this->_nickname = $value;
        return $this;
    }

}
