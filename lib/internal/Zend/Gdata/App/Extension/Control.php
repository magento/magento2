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
 * @version    $Id: Control.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_Extension
 */
#require_once 'Zend/Gdata/App/Extension.php';

/**
 * @see Zend_Gdata_App_Extension_Draft
 */
#require_once 'Zend/Gdata/App/Extension/Draft.php';

/**
 * Represents the app:control element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_Extension_Control extends Zend_Gdata_App_Extension
{

    protected $_rootNamespace = 'app';
    protected $_rootElement = 'control';
    protected $_draft = null;

    public function __construct($draft = null)
    {
        parent::__construct();
        $this->_draft = $draft;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_draft != null) {
            $element->appendChild($this->_draft->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('app') . ':' . 'draft':
            $draft = new Zend_Gdata_App_Extension_Draft();
            $draft->transferFromDOM($child);
            $this->_draft = $draft;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * @return Zend_Gdata_App_Extension_Draft
     */
    public function getDraft()
    {
        return $this->_draft;
    }

    /**
     * @param Zend_Gdata_App_Extension_Draft $value
     * @return Zend_Gdata_App_Entry Provides a fluent interface
     */
    public function setDraft($value)
    {
        $this->_draft = $value;
        return $this;
    }

}
