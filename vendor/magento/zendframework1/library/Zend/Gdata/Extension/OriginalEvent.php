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
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Feed
 */
#require_once 'Zend/Gdata/Feed.php';

/**
 * @see Zend_Gdata_When
 */
#require_once 'Zend/Gdata/Extension/When.php';

/**
 * Represents the gd:originalEvent element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_OriginalEvent extends Zend_Gdata_Extension
{

    protected $_rootElement = 'originalEvent';
    protected $_id = null;
    protected $_href = null;
    protected $_when = null;

    public function __construct($id = null, $href = null, $when = null)
    {
        parent::__construct();
        $this->_id = $id;
        $this->_href = $href;
        $this->_when = $when;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_id !== null) {
            $element->setAttribute('id', $this->_id);
        }
        if ($this->_href !== null) {
            $element->setAttribute('href', $this->_href);
        }
        if ($this->_when !== null) {
            $element->appendChild($this->_when->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'id':
            $this->_id = $attribute->nodeValue;
            break;
        case 'href':
            $this->_href = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'when';
                $when = new Zend_Gdata_Extension_When();
                $when->transferFromDOM($child);
                $this->_when = $when;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }

    public function getHref()
    {
        return $this->_href;
    }

    public function setHref($value)
    {
        $this->_href = $value;
        return $this;
    }

    public function getWhen()
    {
        return $this->_when;
    }

    public function setWhen($value)
    {
        $this->_when = $value;
        return $this;
    }


}
