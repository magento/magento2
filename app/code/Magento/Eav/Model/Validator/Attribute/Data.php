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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * EAV attribute data validator
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\Attribute;

class Data extends \Magento\Framework\Validator\AbstractValidator
{
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
     * @var \Magento\Eav\Model\AttributeDataFactory
     */
    protected $_attrDataFactory;

    /**
     * @param \Magento\Eav\Model\AttributeDataFactory $attrDataFactory
     */
    public function __construct(\Magento\Eav\Model\AttributeDataFactory $attrDataFactory)
    {
        $this->_attrDataFactory = $attrDataFactory;
    }

    /**
     * Set list of attributes for validation in isValid method.
     *
     * @param Attribute[] $attributes
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Validate EAV model attributes with data models
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return bool
     */
    public function isValid($entity)
    {
        /** @var $attributes Attribute[] */
        $attributes = $this->_getAttributes($entity);

        $data = array();
        if ($this->_data) {
            $data = $this->_data;
        } elseif ($entity instanceof \Magento\Framework\Object) {
            $data = $entity->getData();
        }

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            $dataModel = $this->_attrDataFactory->create($attribute, $entity);
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
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return array
     */
    protected function _getAttributes($entity)
    {
        /** @var \Magento\Eav\Model\Attribute[] $attributes */
        $attributes = array();

        if ($this->_attributes) {
            $attributes = $this->_attributes;
        } elseif ($entity instanceof \Magento\Framework\Model\AbstractModel &&
            $entity->getResource() instanceof \Magento\Eav\Model\Entity\AbstractEntity
        ) { // $entity is EAV-model
            /** @var \Magento\Eav\Model\Entity\Type $entityType */
            $entityType = $entity->getEntityType();
            $attributes = $entityType->getAttributeCollection()->getItems();
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
     * Add error messages
     *
     * @param string $code
     * @param array $messages
     * @return void
     */
    protected function _addErrorMessages($code, array $messages)
    {
        if (!array_key_exists($code, $this->_messages)) {
            $this->_messages[$code] = $messages;
        } else {
            $this->_messages[$code] = array_merge($this->_messages[$code], $messages);
        }
    }
}
