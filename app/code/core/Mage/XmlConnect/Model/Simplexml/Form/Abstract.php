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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect simple xml abstract from class
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Simplexml_Form_Abstract extends Varien_Object
{
    /**
     * Element unique id
     *
     * @var string
     */
    protected $_id;

    /**
     * Form level elements collection
     *
     * @var Mage_XmlConnect_Model_Simplexml_Form_Element_Collection
     */
    protected $_elements;

    /**
     * Element type classes
     *
     * @var array
     */
    protected $_types = array();

    /**
     * From Simplexml object
     *
     * @var Mage_XmlConnect_Model_Simplexml_Element
     */
    protected $_xml;

    /**
     * Main element node
     *
     * @var string
     */
    protected $_mainNode = 'form';

    /**
     * Is name attribute required
     *
     * @var bool
     */
    protected $_nameRequired = true;

    /**
     * Custom attributes array
     *
     * @var array
     */
    protected $_customAttributes = array();

    /**
     * Init form model
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_prepareXmlObject();
    }

    /**
     * Init form parent Simplexml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected function _prepareXmlObject()
    {
        $this->setXmlObject(
            Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<' . $this->_mainNode . '></' . $this->_mainNode . '>')
        );
        return $this;
    }

    /**
     * Get base simple xml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getXmlObject()
    {
        return $this->_xml;
    }

    /**
     * Set simple xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xml
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function setXmlObject(Mage_XmlConnect_Model_Simplexml_Element $xml)
    {
        $this->_xml = $xml;
        return $this;
    }

    /**
     * Get element id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set element id
     *
     * @param $id
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function setId($id)
    {
        $this->_id = $id;
        $this->setData('xml_id', $id);
        return $this;
    }

    /**
     * Get element id
     *
     * @return string
     */
    public function getXmlId()
    {
        return $this->getXmlIdPrefix() . $this->getData('xml_id') . $this->getXmlIdSuffix();
    }

    /**
     * Add form element type
     *
     * @param string $type
     * @param string $className
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function addType($type, $className)
    {
        $this->_types[$type] = $className;
        return $this;
    }

    /**
     * Get elements collection
     *
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Collection
     */
    public function getElements()
    {
        if (empty($this->_elements)) {
            $this->_elements = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Form_Element_Collection', $this);
        }
        return $this->_elements;
    }

    /**
     * Add form element
     *
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract $element
     * @param bool|string $after
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function addElement(Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract $element, $after = false)
    {
        $element->setForm($this);
        $this->getElements()->add($element, $after);
        return $this;
    }

    /**
     * Add child element
     *
     * if $after parameter is false - add element to the end of a collection
     * if $after parameter is ^ - prepend element to the beginning of a collection
     * if $after parameter is string - add element after the element with some id
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param mixed $after
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function addField($elementId, $type, $config, $after = false)
    {
        if (isset($this->_types[$type])) {
            $className = $this->_types[$type];
        } else {
            $className = 'Mage_XmlConnect_Model_Simplexml_Form_Element_' . uc_words($type);
        }

        $element = Mage::getModel($className, $config);
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Remove element from collection
     *
     * @param string $elementId
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function removeField($elementId)
    {
        $this->getElements()->remove($elementId);
        return $this;
    }

    /**
     * Add fieldset element
     *
     * @param string $elementId
     * @param array $config
     * @param bool|string $after
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Fieldset
     */
    public function addFieldset($elementId, $config = array(), $after = false)
    {
        $element = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Form_Element_Fieldset', $config);
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Add validator element
     *
     * @param array $config
     * @param bool|string $after
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Validator
     */
    public function addValidator($config = array(), $after = false)
    {
        $element = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Form_Element_Validator', $config);
        $element->setId($this->getXmlId());
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Get array of existing elements
     *
     * @param array $arrAttributes
     * @return array
     */
    public function __toArray(array $arrAttributes = array())
    {
        $res = array();
        $res['config']  = $this->getData();
        $res['formElements']= array();
        foreach ($this->getElements() as $element) {
            $res['formElements'][] = $element->toArray();
        }
        return $res;
    }

    /**
     * Return allowed xml form attributes
     *
     * @return array
     */
    public function getXmlAttributes()
    {
        return array('enctype');
    }

    /**
     * Required form attribute array
     *
     * @return array
     */
    public function getRequiredXmlAttributes()
    {
        return array('action' => null, 'method' => 'post');
    }

    /**
     * Get after element xml
     *
     * @return array|Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getAfterElementXml()
    {
        return $this->getData('after_element_xml');
    }

    /**
     * Get xml object attributes
     *
     * @param array $attributes
     * @return array
     */
    public function getXmlObjAttributes($attributes = array())
    {
        $data = array();
        if (empty($attributes)) {
            $attributes = array_keys($this->_data);
        }

        foreach ($this->_data as $key => $value) {
            if (in_array($key, $attributes)) {
                $data[$key] = $value;
            }
        }
        ksort($data);
        return $data;
    }

    /**
     * Get object attributes array
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = array_merge($this->getXmlAttributes(), $this->getCustomAttributes());
        return $this->getXmlObjAttributes($attributes);
    }

    /**
     * Add after element xml to object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function addAfterXmlElementToObj(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        if ($this->_checkXmlInstance($this->getAfterElementXml())) {
            $xmlObj->appendChild($this->getAfterElementXml());
        } elseif (is_array($this->getAfterElementXml())) {
            foreach ($this->getAfterElementXml() as $afterElement) {
                if (!$this->_checkXmlInstance($afterElement)) {
                    continue;
                }
                $xmlObj->appendChild($afterElement);
            }
        }
        return $this;
    }

    /**
     * Add required attributes to element
     *
     * @throws Mage_Core_Exception
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected  function _addRequiredAttributes(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        $this->_addId($xmlObj);
        $this->_addName($xmlObj);

        foreach ($this->getRequiredXmlAttributes() as $attribute => $defValue) {
            $data = $this->getData($this->_underscore($attribute));
            if ($data) {
                $xmlObj->addAttribute($attribute, $xmlObj->xmlAttribute($data));
            } elseif(null !== $defValue){
                $xmlObj->addAttribute($attribute, $xmlObj->xmlAttribute($defValue));
            } else {
                Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('%s attribute is required.', $attribute));
            }
        }
        return $this;
    }

    /**
     * Add validator to element xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected function _addValidator(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        return $this;
    }

    /**
     * Add form id to element
     *
     * @throws Mage_Core_Exception
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected function _addId(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        if ($this->getXmlId()) {
            $xmlObj->addAttribute('id', $xmlObj->xmlAttribute($this->getXmlId()));
        } else {
            Mage::throwException(
                Mage::helper('Mage_XmlConnect_Helper_Data')->__('"id" attribute is required for a "%s" field.', $this->getType())
            );
        }
        return $this;
    }

    /**
     * Add form name to element
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected function _addName(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        if ($this->getName()) {
            $name = $this->getName();
        } elseif($this->getNameRequired()) {
            $name = $this->getXmlId();
        }

        if (isset($name)) {
            $xmlObj->addAttribute('name', $xmlObj->xmlAttribute($name));
        }

        return $this;
    }

    /**
     * Is object instance of Simplexml object
     *
     * @param $object
     * @return bool
     */
    protected function _checkXmlInstance($object)
    {
        return $object instanceof Mage_XmlConnect_Model_Simplexml_Element;
    }

    /**
     * Get is name required attribute
     *
     * @return boolean
     */
    public function getNameRequired()
    {
        return $this->_nameRequired;
    }

    /**
     * Set is name required attribute
     *
     * @param boolean $nameRequired
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function setNameRequired($nameRequired)
    {
        $this->_nameRequired = $nameRequired;
        return $this;
    }

    /**
     * Get custom attributes
     *
     * @return array
     */
    public function getCustomAttributes()
    {
        return $this->_customAttributes;
    }

    /**
     * Set custom attributes
     *
     * @param array $customAttributes
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function setCustomAttributes(array $customAttributes)
    {
        $this->_customAttributes = $customAttributes;
        return $this;
    }

    /**
     * Check value and return as array - attribute => value
     *
     * @param string $attribute
     * @param mixed $value
     * @return array
     */
    public function checkAttribute($attribute, $value = null)
    {
        if (null === $value) {
            $value = $this->getData($attribute);
        }

        if (null !== $value) {
            return array($attribute => $value);
        }
        return array();
    }
}
