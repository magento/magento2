<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect simple xml form fieldset
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Simplexml_Form_Element_Fieldset
    extends Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
{
    /**
     * Sort child elements by specified data key
     *
     * @var string
     */
    protected $_sortChildrenByKey = '';

    /**
     * Children sort direction
     *
     * @var int
     */
    protected $_sortChildrenDirection = SORT_ASC;

    /**
     * Main element node
     *
     * @var string
     */
    protected $_mainNode = 'fieldset';

    /**
     * Is name attribute required
     *
     * @var bool
     */
    protected $_nameRequired = false;

    /**
     * Init fieldset object
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_renderer = Mage_XmlConnect_Model_Simplexml_Form::getFieldsetRenderer();
        $this->setType('fieldset');
    }

    /**
     * Get fieldset element object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getElementXml()
    {
        $xmlObj = $this->getXmlObject();
        $this->_addRequiredAttributes($xmlObj);
        foreach ($this->getAttributes() as $key => $val) {
            $xmlObj->addAttribute($key, $xmlObj->xmlAttribute($val));
        }
        foreach ($this->getChildrenXml(false) as $element) {
            $xmlObj->appendChild($element);
        }
        foreach ($this->getChildrenXml(true) as $fieldset) {
            $xmlObj->appendChild($fieldset);
        }
        $this->addAfterXmlElementToObj($xmlObj);
        return $xmlObj;
    }

    /**
     * Default element attribute array
     *
     * @return array
     */
    public function getXmlAttributes()
    {
        return array('title', 'disabled');
    }

    /**
     * Required element attribute array
     *
     * @return array
     */
    public function getRequiredXmlAttributes()
    {
        return array();
    }

    /**
     * Get children array of elements
     *
     * @param bool $isFieldset
     * @return array
     */
    public function getChildrenXml($isFieldset = false)
    {
        $result = array();
        foreach ($this->getSortedElements() as $element) {
            if ($this->_checkFieldset($element, $isFieldset)) {
                $result[] = $element->toXmlObject();
            }
        }
        return $result;
    }

    /**
     * Check weather is element a fieldset
     *
     * @param Mage_XmlConnect_Model_Simplexml_Form_Abstract $element
     * @param bool $equal
     * @return bool
     */
    protected function _checkFieldset($element, $equal = true) {
        if ($equal) {
            return $element->getType() == 'fieldset';
        } else {
            return $element->getType() != 'fieldset';
        }
    }

    /**
     * Add field element to fieldset
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param boolean $after
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    public function addField($elementId, $type, $config, $after = false)
    {
        $element = parent::addField($elementId, $type, $config, $after);
        if ($renderer = Mage_XmlConnect_Model_Simplexml_Form::getFieldsetElementRenderer()) {
            $element->setRenderer($renderer);
        }
        return $element;
    }

    /**
     * Commence sorting elements by values by specified data key
     *
     * @param string $key
     * @param int $direction
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Fieldset
     */
    public function setSortElementsByAttribute($key, $direction = SORT_ASC)
    {
        $this->_sortChildrenByKey = $key;
        $this->_sortDirection = $direction;
        return $this;
    }

    /**
     * Get sorted elements as array
     *
     * @return array
     */
    public function getSortedElements()
    {
        $elements = array();
        // sort children by value by specified key
        if ($this->_sortChildrenByKey) {
            $sortKey = $this->_sortChildrenByKey;
            $uniqueIncrement = 0; // in case if there are elements with same values
            /** @var Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract $element */
            foreach ($this->getElements() as $element) {
                $key = '_' . $uniqueIncrement;
                if ($element->hasData($sortKey)) {
                    $key = $element->getDataUsingMethod($sortKey) . $key;
                }
                $elements[$key] = $element;
                $uniqueIncrement++;
            }

            if ($this->_sortDirection == SORT_ASC) {
                ksort($elements, $this->_sortChildrenDirection);
            } else {
                krsort($elements, $this->_sortChildrenDirection);
            }

            $elements = array_values($elements);
        } else {
            foreach ($this->getElements() as $element) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
}
