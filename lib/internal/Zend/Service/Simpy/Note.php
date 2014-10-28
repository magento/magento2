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
 * @version    $Id: Note.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_Note
{
    /**
     * Private access type
     *
     * @var string
     */
    const ACCESSTYPE_PRIVATE = 'private';

    /**
     * Public access type
     *
     * @var string
     */
    const ACCESSTYPE_PUBLIC  = 'public';

    /**
     * Access type assigned to the note
     *
     * @var string
     */
    protected $_accessType;

    /**
     * ID of the note
     *
     * @var int
     */
    protected $_id;

    /**
     * URI of the note
     *
     * @var string
     */
    protected $_uri;

    /**
     * Date of the last modification made to the note
     *
     * @var string
     */
    protected $_modDate;

    /**
     * Date the note was added
     *
     * @var string
     */
    protected $_addDate;

    /**
     * Title of to the note
     *
     * @var string
     */
    protected $_title;

    /**
     * Tags assigned to the note
     *
     * @var array
     */
    protected $_tags;

    /**
     * Description of the note
     *
     * @var string
     */
    protected $_description;

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

        $this->_uri = $xpath->evaluate('/note/uri')->item(0)->nodeValue;
        $this->_id = substr($this->_uri, strrpos($this->_uri, '=') + 1);
        $this->_modDate = trim($xpath->evaluate('/note/modDate')->item(0)->nodeValue);
        $this->_addDate = trim($xpath->evaluate('/note/addDate')->item(0)->nodeValue);
        $this->_title = $xpath->evaluate('/note/title')->item(0)->nodeValue;
        $this->_description = $xpath->evaluate('/note/description')->item(0)->nodeValue;

        $list = $xpath->query('/note/tags/tag');
        $this->_tags = array();

        for ($x = 0; $x < $list->length; $x++) {
            $this->_tags[$x] = $list->item($x)->nodeValue;
        }
    }

    /**
     * Returns the access type assigned to the note
     *
     * @see    ACCESSTYPE_PRIVATE
     * @see    ACCESSTYPE_PUBLIC
     * @return string
     */
    public function getAccessType()
    {
        return $this->_accessType;
    }

    /**
     * Returns the ID of the note
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the URI of the note
     *
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Returns the date of the last modification made to the note
     *
     * @return string
     */
    public function getModDate()
    {
        return $this->_modDate;
    }

    /**
     * Returns the date the note was added
     *
     * @return string
     */
    public function getAddDate()
    {
        return $this->_addDate;
    }

    /**
     * Returns the title assigned to the note
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Returns the tags assigned to the note
     *
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Returns the description assigned to the note
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
}
