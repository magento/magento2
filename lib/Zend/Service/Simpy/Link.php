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
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Link.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_Link
{
    /**
     * Private access type
     *
     * @var string
     */
    const ACCESSTYPE_PRIVATE = '0';

    /**
     * Public access type
     *
     * @var string
     */
    const ACCESSTYPE_PUBLIC  = '1';

    /**
     * Access type assigned to the link
     *
     * @var string
     */
    protected $_accessType;

    /**
     * URL of the link
     *
     * @var string
     */
    protected $_url;

    /**
     * Date of the last modification made to the link
     *
     * @var string
     */
    protected $_modDate;

    /**
     * Date the link was added
     *
     * @var string
     */
    protected $_addDate;

    /**
     * Title assigned to the link
     *
     * @var string
     */
    protected $_title;

    /**
     * Nickname assigned to the link
     *
     * @var string
     */
    protected $_nickname;

    /**
     * Tags assigned to the link
     *
     * @var array
     */
    protected $_tags;

    /**
     * Note assigned to the link
     *
     * @var string
     */
    protected $_note;

    /**
     * Constructor to initialize the object with data
     *
     * @param  DOMNode $node Individual <link> node from a parsed response from
     *                       a GetLinks operation
     * @return void
     */
    public function __construct($node)
    {
        $this->_accessType = $node->attributes->getNamedItem('accessType')->nodeValue;

        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($node, true));
        $xpath = new DOMXPath($doc);

        $this->_url = $xpath->evaluate('/link/url')->item(0)->nodeValue;
        $this->_modDate = $xpath->evaluate('/link/modDate')->item(0)->nodeValue;
        $this->_addDate = $xpath->evaluate('/link/addDate')->item(0)->nodeValue;
        $this->_title = $xpath->evaluate('/link/title')->item(0)->nodeValue;
        $this->_nickname = $xpath->evaluate('/link/nickname')->item(0)->nodeValue;
        $this->_note = $xpath->evaluate('/link/note')->item(0)->nodeValue;

        $list = $xpath->query('/link/tags/tag');
        $this->_tags = array();

        for ($x = 0; $x < $list->length; $x++) {
            $this->_tags[$x] = $list->item($x)->nodeValue;
        }
    }

    /**
     * Returns the access type assigned to the link
     *
     * @see ACCESSTYPE_PRIVATE
     * @see ACCESSTYPE_PUBLIC
     * @return string
     */
    public function getAccessType()
    {
        return $this->_accessType;
    }

    /**
     * Returns the URL of the link
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Returns the date of the last modification made to the link
     *
     * @return string
     */
    public function getModDate()
    {
        return $this->_modDate;
    }

    /**
     * Returns the date the link was added
     *
     * @return string
     */
    public function getAddDate()
    {
        return $this->_addDate;
    }

    /**
     * Returns the title assigned to the link
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Returns the nickname assigned to the link
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->_nickname;
    }

    /**
     * Returns the tags assigned to the link
     *
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Returns the note assigned to the link
     *
     * @return string
     */
    public function getNote()
    {
        return $this->_note;
    }
}
