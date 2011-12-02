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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity/Attribute/Model - attribute frontend abstract
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
    implements Mage_Eav_Model_Entity_Attribute_Frontend_Interface
{

    /**
     * Reference to the attribute instance
     *
     * @var Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected $_attribute;

    /**
     * Set attribute instance
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Get attribute type for user interface form
     *
     * @return string
     */
    public function getInputType()
    {
        return $this->getAttribute()->getFrontendInput();
    }

    /**
     * Retreive lable
     *
     * @return string
     */
    public function getLabel()
    {
        $label = $this->getAttribute()->getFrontendLabel();
        if (($label === null) || $label == '') {
            $label = $this->getAttribute()->getAttributeCode();
        }

        return $label;
    }

    /**
     * Retreive attribute value
     *
     * @param $object
     * @return mixed
     */
    public function getValue(Varien_Object $object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if (in_array($this->getConfigField('input'), array('select','boolean'))) {
            $valueOption = $this->getOption($value);
            if (!$valueOption) {
                $opt     = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Source_Boolean');
                $options = $opt->getAllOptions();
                if ($options) {
                    foreach ($options as $option) {
                        if ($option['value'] == $value) {
                            $valueOption = $option['label'];
                        }
                    }
                }
            }
            $value = $valueOption;
        } elseif ($this->getConfigField('input') == 'multiselect') {
            $value = $this->getOption($value);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
        }

        return $value;
    }

    /**
     * Checks if attribute is visible on frontend
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->getConfigField('frontend_visible');
    }

    /**
     * Retreive frontend class
     *
     * @return string
     */
    public function getClass()
    {
        $out    = array();
        $out[]  = $this->getAttribute()->getFrontendClass();
        if ($this->getAttribute()->getIsRequired()) {
            $out[]  = 'required-entry';
        }

        $inputRuleClass = $this->_getInputValidateClass();
        if ($inputRuleClass) {
             $out[] = $inputRuleClass;
        }
        if (!empty($out)) {
            $out = implode(' ', $out);
        } else {
            $out = '';
        }
        return $out;
    }

     /**
     * Return validate class by attribute input validation rule
     *
     * @return string|false
     */
    protected function _getInputValidateClass()
    {
        $class          = false;
        $validateRules  = $this->getAttribute()->getValidateRules();
        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
                case 'alphanumeric':
                    $class = 'validate-alphanum';
                    break;
                case 'numeric':
                    $class = 'validate-digits';
                    break;
                case 'alpha':
                    $class = 'validate-alpha';
                    break;
                case 'email':
                    $class = 'validate-email';
                    break;
                case 'url':
                    $class = 'validate-url';
                    break;
                default:
                    $class = false;
                    break;
            }
        }
        return $class;
    }

    /**
     * Reireive config field
     *
     * @param string $fieldName
     * @return mixed
     */
    public function getConfigField($fieldName)
    {
        return $this->getAttribute()->getData('frontend_' . $fieldName);
    }

    /**
     * Get select options in case it's select box and options source is defined
     *
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->getAttribute()->getSource()->getAllOptions();
    }

    /**
     * Retreive option by option id
     *
     * @param int $optionId
     * @return mixed|boolean
     */
    public function getOption($optionId)
    {
        $source = $this->getAttribute()->getSource();
        if ($source) {
            return $source->getOptionText($optionId);
        }
        return false;
    }

    /**
     * Retrieve Input Renderer Class
     *
     * @return string
     */
    public function getInputRendererClass() {
        $className = $this->getAttribute()->getData('frontend_input_renderer');
        if ($className) {
            return Mage::getConfig()->getBlockClassName($className);
        }
        return null;
    }
}
