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
 * Xmlconnect form element abstract class
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
    extends Mage_XmlConnect_Model_Simplexml_Form_Abstract
{
    /**
     * Element type
     *
     * @var string
     */
    protected $_type;

    /**
     * From element object
     *
     * @var Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected $_form;

    /**
     * Main element node
     *
     * @var string
     */
    protected $_mainNode = 'field';

    /**
     * Element renderer object
     *
     * @var object
     */
    protected $_renderer;

    /**
     * Init element object abstract
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_renderer = Mage_XmlConnect_Model_Simplexml_Form::getElementRenderer();
    }

    /**
     * Add form element
     *
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract $element
     * @param bool $after
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function addElement(Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract $element, $after = false)
    {
        if ($this->getForm()) {
            $this->getForm()->checkElementId($element->getId());
            $this->getForm()->addElementToCollection($element);
        }

        parent::addElement($element, $after);
        return $this;
    }

    /**
     * Get element type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get form object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Get element id
     *
     * @return string
     */
    public function getXmlId()
    {
        return $this->getForm()->getXmlIdPrefix() . $this->getData('xml_id') . $this->getForm()->getXmlIdSuffix();
    }

    /**
     * Get element name
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->getData('name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * Set element type
     *
     * @param $type
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    public function setType($type)
    {
        $this->_type = $type;
        $this->setData('type', $type);
        return $this;
    }

    /**
     * Set form object
     *
     * @param $form
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Remove field from a form
     *
     * @param $elementId
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function removeField($elementId)
    {
        $this->getForm()->removeField($elementId);
        return parent::removeField($elementId);
    }

    /**
     * Element attributes array
     *
     * @return array
     */
    public function getXmlAttributes()
    {
        return array('title', 'required', 'disabled', 'visible', 'relation');
    }

    /**
     * Required element attribute array
     *
     * @return array
     */
    public function getRequiredXmlAttributes()
    {
        return array('label' => null, 'type' => null);
    }

    /**
     * Retrieve element xml object
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
        $this->_addValue($xmlObj);

        foreach ($this->getElements() as $element) {
            if ($element->getType() == 'validator') {
                $xmlObj->appendChild($element->toXmlObject());
            }
        }

        $this->addAfterXmlElementToObj($xmlObj);

        return $xmlObj;
    }

    /**
     * Get escaped value
     *
     * @param string $index
     * @return string|null
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);

        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }

        return $value;
    }

    /**
     * Set element renderer
     *
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    public function setRenderer(Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    /**
     * Get element renderer
     *
     * @return object
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * Add value to element
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    protected function _addValue(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        if ($this->getEscapedValue()) {
            $xmlObj->addAttribute('value', $xmlObj->xmlAttribute($this->getEscapedValue()));
        }
        return $this;
    }

    /**
     * Retrieve default form xml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getDefaultXml()
    {
        $xml = $this->getData('default_xml');
        if (null === $xml) {
            $xml = $this->getElementXml();
        }
        return $xml;
    }

    /**
     * Get element xml as string
     *
     * @return string
     */
    public function getXml()
    {
        return $this->toXmlObject->asNiceXml();
    }

    /**
     * Retrieve form xml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function toXmlObject()
    {
        if ($this->_renderer) {
            return $this->_renderer->render($this);
        } else {
            return $this->getDefaultXml();
        }
    }

    /**
     * Get xml container id
     *
     * @return string
     */
    public function getXmlContainerId()
    {
        if ($this->hasData('container_id')) {
            return $this->getData('container_id');
        } elseif ($idPrefix = $this->getForm()->getFieldContainerIdPrefix()) {
            return $idPrefix . $this->getId();
        }
        return '';
    }
}
