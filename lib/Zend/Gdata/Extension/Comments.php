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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Comments.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Extension_FeedLink
 */
#require_once 'Zend/Gdata/Extension/FeedLink.php';

/**
 * Represents the gd:comments element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_Comments extends Zend_Gdata_Extension
{

    protected $_rootElement = 'comments';
    protected $_rel = null;
    protected $_feedLink = null;

    public function __construct($rel = null, $feedLink = null)
    {
        parent::__construct();
        $this->_rel = $rel;
        $this->_feedLink = $feedLink;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_rel !== null) {
            $element->setAttribute('rel', $this->_rel);
        }
        if ($this->_feedLink !== null) {
            $element->appendChild($this->_feedLink->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'feedLink';
                $feedLink = new Zend_Gdata_Extension_FeedLink();
                $feedLink->transferFromDOM($child);
                $this->_feedLink = $feedLink;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'rel':
            $this->_rel = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getRel()
    {
        return $this->_rel;
    }

    public function setRel($value)
    {
        $this->_rel = $value;
        return $this;
    }

    public function getFeedLink()
    {
        return $this->_feedLink;
    }

    public function setFeedLink($value)
    {
        $this->_feedLink = $value;
        return $this;
    }

}
