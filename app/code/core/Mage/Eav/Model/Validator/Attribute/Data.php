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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * EAV attribute data validator
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Validator_Attribute_Data extends Magento_Validator_ValidatorAbstract
{
    /**
     * @var array
     */
    protected $_messages = array();

    /**
     * @var array
     */
    protected $_attributes = array();

    /**
     * @var array
     */
    protected $_attributesWhiteList = array();

    /**
     * @var array
     */
    protected $_attributesBlackList = array();

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var Mage_Eav_Model_Attribute_Data
     */
    protected $_dataModelFactory;

    /**
     * Set list of attributes for validation in isValid method.
     *
     * @param Mage_Eav_Model_Attribute[] $attributes
     * @return Mage_Eav_Model_Validator_Attribute_Data
     */
    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
        return $this;
    }

    /**
     * Set codes of attributes that should be filtered in validation process.
     *
     * All attributes not in this list 't be involved in validation.
     *
     * @param array $attributesCodes
     * @return Mage_Eav_Model_Validator_Attribute_Data
     */
    public function setAttributesWhiteList(array $attributesCodes)
    {
        $this->_attributesWhiteList = $attributesCodes;
        return $this;
    }

    /**
     * Set codes of attributes that should be excluded in validation process.
     *
     * All attributes in this list won't be involved in validation.
     *
     * @param array $attributesCodes
     * @return Mage_Eav_Model_Validator_Attribute_Data
     */
    public function setAttributesBlackList(array $attributesCodes)
    {
        $this->_attributesBlackList = $attributesCodes;
        return $this;
    }

    /**
     * Set data for validation in isValid method.
     *
     * @param array $data
     * @return Mage_Eav_Model_Validator_Attribute_Data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Validate EAV model attributes with data models
     *
     * @param Mage_Core_Model_Abstract $entity
     * @return bool
     */
    public function isValid($entity)
    {
        /** @var $attributes Mage_Eav_Model_Attribute[] */
        $attributes = $this->_getAttributes($entity);

        $data = array();
        if ($this->_data) {
            $data = $this->_data;
        } elseif ($entity instanceof Varien_Object) {
            $data = $entity->getData();
        }

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            $dataModel = $this->getAttributeDataModelFactory()->factory($attribute, $entity);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attributeCode])) {
                $data[$attributeCode] = null;
            }
            $result = $dataModel->validateValue($data[$attributeCode]);
            if (true !== $result) {
                $this->_addErrorMessages($attributeCode, (array)$result);
            }
        }
        return count($this->_messages) == 0;
    }

    /**
     * Get attributes involved in validation.
     *
     * This method return specified $_attributes if they defined by setAttributes method, otherwise if $entity
     * is EAV-model it returns it's all available attributes, otherwise it return empty array.
     *
     * @param mixed $entity
     * @return array
     */
    protected function _getAttributes($entity)
    {
        /** @var Mage_Customer_Model_Attribute[] $attributes */
        $attributes = array();

        if ($this->_attributes) {
            $attributes = $this->_attributes;
        } elseif ($entity instanceof Mage_Core_Model_Abstract
                  && $entity->getResource() instanceof Mage_Eav_Model_Entity_Abstract
        ) { // $entity is EAV-model
            $attributes = $entity->getEntityType()->getAttributeCollection()->getItems();
        }

        $attributesByCode = array();
        $attributesCodes = array();
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributesByCode[$attributeCode] = $attribute;
            $attributesCodes[] = $attributeCode;
        }

        $ignoreAttributes = $this->_attributesBlackList;
        if ($this->_attributesWhiteList) {
            $ignoreAttributes = array_merge(
                $ignoreAttributes,
                array_diff($attributesCodes, $this->_attributesWhiteList)
            );
        }

        foreach ($ignoreAttributes as $attributeCode) {
            unset($attributesByCode[$attributeCode]);
        }

        return $attributesByCode;
    }

    /**
     * Get factory object for creating Attribute Data Model
     *
     * @return Mage_Eav_Model_Attribute_Data
     */
    public function getAttributeDataModelFactory()
    {
        if (!$this->_dataModelFactory) {
            $this->_dataModelFactory = new Mage_Eav_Model_Attribute_Data;
        }
        return $this->_dataModelFactory;
    }

    /**
     * Set factory object for creating Attribute Data Model
     *
     * @param Mage_Eav_Model_Attribute_Data $factory
     * @return Mage_Eav_Model_Validator_Attribute_Data
     */
    public function setAttributeDataModelFactory($factory)
    {
        $this->_dataModelFactory = $factory;
        return $this;
    }

    /**
     * Add error messages
     *
     * @param string $code
     * @param array $messages
     */
    protected function _addErrorMessages($code, array $messages)
    {
        if (!array_key_exists($code, $this->_messages)) {
            $this->_messages[$code] = $messages;
        } else {
            $this->_messages[$code] = array_merge($this->_messages[$code], $messages);
        }
    }

    /**
     * Get validation messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
