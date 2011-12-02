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
 * @version    $Id: Author.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Utils
 */
#require_once 'Zend/Service/Technorati/Utils.php';


/**
 * Represents a weblog Author object. It usually belongs to a Technorati account.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_Author
{
    /**
     * Author first name
     *
     * @var     string
     * @access  protected
     */
    protected $_firstName;

    /**
     * Author last name
     *
     * @var     string
     * @access  protected
     */
    protected $_lastName;

    /**
     * Technorati account username
     *
     * @var     string
     * @access  protected
     */
    protected $_username;

    /**
     * Technorati account description
     *
     * @var     string
     * @access  protected
     */
    protected $_description;

    /**
     * Technorati account biography
     *
     * @var     string
     * @access  protected
     */
    protected $_bio;

    /**
     * Technorati account thumbnail picture URL, if any
     *
     * @var     null|Zend_Uri_Http
     * @access  protected
     */
    protected $_thumbnailPicture;


    /**
     * Constructs a new object from DOM Element.
     *
     * @param   DomElement $dom the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $xpath = new DOMXPath($dom->ownerDocument);

        $result = $xpath->query('./firstname/text()', $dom);
        if ($result->length == 1) $this->setFirstName($result->item(0)->data);

        $result = $xpath->query('./lastname/text()', $dom);
        if ($result->length == 1) $this->setLastName($result->item(0)->data);

        $result = $xpath->query('./username/text()', $dom);
        if ($result->length == 1) $this->setUsername($result->item(0)->data);

        $result = $xpath->query('./description/text()', $dom);
        if ($result->length == 1) $this->setDescription($result->item(0)->data);

        $result = $xpath->query('./bio/text()', $dom);
        if ($result->length == 1) $this->setBio($result->item(0)->data);

        $result = $xpath->query('./thumbnailpicture/text()', $dom);
        if ($result->length == 1) $this->setThumbnailPicture($result->item(0)->data);
    }


    /**
     * Returns Author first name.
     *
     * @return  string  Author first name
     */
    public function getFirstName() {
        return $this->_firstName;
    }

    /**
     * Returns Author last name.
     *
     * @return  string  Author last name
     */
    public function getLastName() {
        return $this->_lastName;
    }

    /**
     * Returns Technorati account username.
     *
     * @return  string  Technorati account username
     */
    public function getUsername() {
        return $this->_username;
    }

    /**
     * Returns Technorati account description.
     *
     * @return  string  Technorati account description
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * Returns Technorati account biography.
     *
     * @return  string  Technorati account biography
     */
    public function getBio() {
        return $this->_bio;
    }

    /**
     * Returns Technorati account thumbnail picture.
     *
     * @return  null|Zend_Uri_Http  Technorati account thumbnail picture
     */
    public function getThumbnailPicture() {
        return $this->_thumbnailPicture;
    }


    /**
     * Sets author first name.
     *
     * @param   string $input   first Name input value
     * @return  Zend_Service_Technorati_Author  $this instance
     */
    public function setFirstName($input) {
        $this->_firstName = (string) $input;
        return $this;
    }

    /**
     * Sets author last name.
     *
     * @param   string $input   last Name input value
     * @return  Zend_Service_Technorati_Author  $this instance
     */
    public function setLastName($input) {
        $this->_lastName = (string) $input;
        return $this;
    }

    /**
     * Sets Technorati account username.
     *
     * @param   string $input   username input value
     * @return  Zend_Service_Technorati_Author  $this instance
     */
    public function setUsername($input) {
        $this->_username = (string) $input;
        return $this;
    }

    /**
     * Sets Technorati account biography.
     *
     * @param   string $input   biography input value
     * @return  Zend_Service_Technorati_Author  $this instance
     */
    public function setBio($input) {
        $this->_bio = (string) $input;
        return $this;
    }

    /**
     * Sets Technorati account description.
     *
     * @param   string $input   description input value
     * @return  Zend_Service_Technorati_Author  $this instance
     */
    public function setDescription($input) {
        $this->_description = (string) $input;
        return $this;
    }

    /**
     * Sets Technorati account thumbnail picture.
     *
     * @param   string|Zend_Uri_Http $input thumbnail picture URI
     * @return  Zend_Service_Technorati_Author  $this instance
     * @throws  Zend_Service_Technorati_Exception if $input is an invalid URI
     *          (via Zend_Service_Technorati_Utils::normalizeUriHttp)
     */
    public function setThumbnailPicture($input) {
        $this->_thumbnailPicture = Zend_Service_Technorati_Utils::normalizeUriHttp($input);
        return $this;
    }

}
