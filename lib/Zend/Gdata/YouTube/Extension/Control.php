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
 * @version    $Id: Control.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_Extension_Control
 */
#require_once 'Zend/Gdata/App/Extension/Control.php';

/**
 * @see Zend_Gdata_YouTube_Extension_State
 */
#require_once 'Zend/Gdata/YouTube/Extension/State.php';


/**
 * Specialized Control class for use with YouTube. Enables use of yt extension elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_Extension_Control extends Zend_Gdata_App_Extension_Control
{

    protected $_state = null;

    /**
     * Constructs a new Zend_Gdata_Calendar_Extension_Control object.
     * @see Zend_Gdata_App_Extension_Control#__construct
     * @param Zend_Gdata_App_Extension_Draft $draft
     * @param Zend_Gdata_YouTube_Extension_State $state
     */
    public function __construct($draft = null, $state = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($draft);
        $this->_state = $state;
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
        if ($this->_state != null) {
            $element->appendChild($this->_state->getDOM($element->ownerDocument));
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
        case $this->lookupNamespace('yt') . ':' . 'state':
            $state = new Zend_Gdata_YouTube_Extension_State();
            $state->transferFromDOM($child);
            $this->_state = $state;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Get the value for this element's state attribute.
     *
     * @return Zend_Gdata_YouTube_Extension_State The state element.
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Set the value for this element's state attribute.
     *
     * @param Zend_Gdata_YouTube_Extension_State $value The desired value for this attribute.
     * @return Zend_YouTube_Extension_Control The element being modified.
     */
    public function setState($value)
    {
        $this->_state = $value;
        return $this;
    }

    /**
    * Get the value of this element's state attribute.
    *
    * @return string The state's text value
    */
    public function getStateValue()
    {
      return $this->getState()->getText();
    }

}
