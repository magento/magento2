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
 * XmlConnect simple xml form class
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Simplexml_Form extends Mage_XmlConnect_Model_Simplexml_Form_Abstract
{
    /**
     * All form elements collection
     *
     * @var Mage_XmlConnect_Model_Simplexml_Form_Element_Collection
     */
    protected $_allElements;

    /**
     * form elements index
     *
     * @var array
     */
    protected $_elementsIndex;

    /**#@+
     * Custom form components renderer
     *
     * @var object
     */
    static protected $_defaultElementRenderer;
    static protected $_defaultFieldsetRenderer;
    static protected $_defaultValidatorRenderer;
    static protected $_defaultValidatorRuleRenderer;
    static protected $_defaultFieldsetElementRenderer;
    /**#@-*/

    /**
     * Init simple xml form
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('form');
        $this->_allElements = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Form_Element_Collection', $this);
    }

    /**
     * Set element renderer
     *
     * @static $_defaultElementRenderer
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
     * @return null
     */
    public static function setElementRenderer(
        Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
    ) {
        self::$_defaultElementRenderer = $renderer;
    }

    /**
     * Set validator renderer
     *
     * @static $_defaultValidatorRenderer
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
     * @return null
     */
    public static function setValidatorRenderer(
        Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
    ) {
        self::$_defaultValidatorRenderer = $renderer;
    }

    /**
     * Set validator rule renderer
     *
     * @static $_defaultValidatorRuleRenderer
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
     * @return null
     */
    public static function setValidatorRuleRenderer(
        Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
    ) {
        self::$_defaultValidatorRuleRenderer = $renderer;
    }

    /**
     * Set fieldset renderer
     *
     * @static $_defaultFieldsetRenderer
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
     * @return null
     */
    public static function setFieldsetRenderer(
        Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
    ) {
        self::$_defaultFieldsetRenderer = $renderer;
    }

    /**
     * Set fieldset element renderer
     *
     * @static $_defaultFieldsetElementRenderer
     * @param Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
     * @return null
     */
    public static function setFieldsetElementRenderer(
        Mage_XmlConnect_Model_Simplexml_Form_Element_Renderer_Interface $renderer
    ) {
        self::$_defaultFieldsetElementRenderer = $renderer;
    }

    /**
     * Get element renderer
     *
     * @static $_defaultElementRenderer
     * @return object
     */
    public static function getElementRenderer()
    {
        return self::$_defaultElementRenderer;
    }

    /**
     * Get validator renderer
     *
     * @static $_defaultValidatorRenderer
     * @return object
     */
    public static function getValidatorRenderer()
    {
        return self::$_defaultValidatorRenderer;
    }

    /**
     * Get validator rule renderer
     *
     * @static $_defaultValidatorRuleRenderer
     * @return object
     */
    public static function getValidatorRuleRenderer()
    {
        return self::$_defaultValidatorRuleRenderer;
    }

    /**
     * Get fieldset renderer
     *
     * @static $_defaultFieldsetRenderer
     * @return object
     */
    public static function getFieldsetRenderer()
    {
        return self::$_defaultFieldsetRenderer;
    }

    /**
     * Get fieldset element renderer
     *
     * @static $_defaultFieldsetElementRenderer
     * @return object
     */
    public static function getFieldsetElementRenderer()
    {
        return self::$_defaultFieldsetElementRenderer;
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
        $this->checkElementId($element->getId());
        parent::addElement($element, $after);
        $this->addElementToCollection($element);
        return $this;
    }

    /**
     * Check existing element
     *
     * @param   string $elementId
     * @return  bool
     */
    protected function _elementIdExists($elementId)
    {
        return isset($this->_elementsIndex[$elementId]);
    }

    /**
     * Add form element to collection
     *
     * @param $element
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function addElementToCollection($element)
    {
        $this->_elementsIndex[$element->getId()] = $element;
        $this->_allElements->add($element);
        return $this;
    }

    /**
     * Insure existing element
     *
     * @throws Exception
     * @param $elementId
     * @return bool
     */
    public function checkElementId($elementId)
    {
        if ($this->_elementIdExists($elementId)) {
            throw new Exception(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Element with id %s already exists', $elementId));
        }
        return true;
    }

    /**
     * Get form object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function getForm()
    {
        return $this;
    }

    /**
     * Get element object
     *
     * @param $elementId
     * @return null|Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    public function getElement($elementId)
    {
        if ($this->_elementIdExists($elementId)) {
            return $this->_elementsIndex[$elementId];
        }
        return null;
    }

    /**
     * Set values to the form elements
     *
     * @param array $values
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function setValues($values)
    {
        foreach ($this->_allElements as $element) {
            if (isset($values[$element->getId()])) {
                $element->setValue($values[$element->getId()]);
            } else {
                $element->setValue(null);
            }
        }
        return $this;
    }

    /**
     * Add values to the form elements
     *
     * @param array $values
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function addValues($values)
    {
        if (!is_array($values)) {
            return $this;
        }

        foreach ($values as $elementId => $value) {
            $element = $this->getElement($elementId);
            if ($element) {
                $element->setValue($value);
            }
        }
        return $this;
    }

    /**
     * Remove field from collection
     *
     * @param string $elementId
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function removeField($elementId)
    {
        if ($this->_elementIdExists($elementId)) {
            unset($this->_elementsIndex[$elementId]);
        }
        return $this;
    }

    /**
     * Set field id prefix
     *
     * @param string $prefix
     * @return Mage_XmlConnect_Model_Simplexml_Form
     */
    public function setFieldContainerIdPrefix($prefix)
    {
        $this->setData('field_container_id_prefix', $prefix);
        return $this;
    }

    /**
     * Get field container id prefix
     *
     * @return mixed
     */
    public function getFieldContainerIdPrefix()
    {
        return $this->getData('field_container_id_prefix');
    }

    /**
     * Retrieve form xml object or an array of Simplexml elements
     *
     * @return array|Mage_XmlConnect_Model_Simplexml_Element
     */
    public function toXmlObject()
    {
        $xmlObj = $this->getXmlObject();
        if ($useContainer = $this->getUseContainer()) {
            $this->_addRequiredAttributes($xmlObj);
            foreach ($this->getAttributes() as $key => $val) {
                $xmlObj->addAttribute($key, $xmlObj->xmlAttribute($val));
            }
        }

        foreach ($this->getElements() as $element) {
            $xmlObj->appendChild($element->toXmlObject());
        }

        if (!$useContainer) {
            $result = array();
            foreach ($xmlObj->children() as $child) {
                $result[] = $child;
            }
        }
        return isset($result) ? $result : $xmlObj;
    }

    /**
     * Get from xml as string
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getXml()
    {
        if ($this->getUseContainer()) {
            return $this->toXmlObject()->asNiceXml();
        }
        Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Container is not defined.'));
    }
}
