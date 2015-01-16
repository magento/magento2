<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Type;

/**
 * Import entity abstract product type model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractType
{
    /**
     * Product type attribute sets and attributes parameters.
     *
     * Example: [attr_set_name_1] => array(
     *     [attr_code_1] => array(
     *         'options' => array(),
     *         'type' => 'text', 'price', 'textarea', 'select', etc.
     *         'id' => ..
     *     ),
     *     ...
     * ),
     * ...
     *
     * @var array
     */
    protected $_attributes = [];

    /**
     * Attributes' codes which will be allowed anyway, independently from its visibility property.
     *
     * @var string[]
     */
    protected $_forcedAttributesCodes = [];

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = [];

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [];

    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_specialAttributes = [];

    /**
     * Product entity object.
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $_entityModel;

    /**
     * Product type (simple, etc.).
     *
     * @var string
     */
    protected $_type;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_attrSetColFac;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected $_prodAttrColFac;

    /**
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param array $params
     * @throws \Magento\Framework\Model\Exception
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac,
        array $params
    ) {
        $this->_attrSetColFac = $attrSetColFac;
        $this->_prodAttrColFac = $prodAttrColFac;

        if ($this->isSuitable()) {
            if (!isset(
                $params[0]
            ) || !isset(
                $params[1]
            ) || !is_object(
                $params[0]
            ) || !$params[0] instanceof \Magento\CatalogImportExport\Model\Import\Product
            ) {
                throw new \Magento\Framework\Model\Exception(__('Please correct the parameters.'));
            }
            $this->_entityModel = $params[0];
            $this->_type = $params[1];

            foreach ($this->_messageTemplates as $errorCode => $message) {
                $this->_entityModel->addMessageTemplate($errorCode, $message);
            }
            $this->_initAttributes();
        }
    }

    /**
     * Add attribute parameters to appropriate attribute set.
     *
     * @param string $attrSetName Name of attribute set.
     * @param array $attrParams Refined attribute parameters.
     * @param mixed $attribute
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function _addAttributeParams($attrSetName, array $attrParams, $attribute)
    {
        if (!$attrParams['apply_to'] || in_array($this->_type, $attrParams['apply_to'])) {
            $this->_attributes[$attrSetName][$attrParams['code']] = $attrParams;
        }
        return $this;
    }

    /**
     * Return product attributes for its attribute set specified in row data.
     *
     * @param array|string $attrSetData Product row data or simply attribute set name.
     * @return array
     */
    protected function _getProductAttributes($attrSetData)
    {
        if (is_array($attrSetData)) {
            return $this->_attributes[$attrSetData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET]];
        } else {
            return $this->_attributes[$attrSetData];
        }
    }

    /**
     * Initialize attributes parameters for all attributes' sets.
     *
     * @return $this
     */
    protected function _initAttributes()
    {
        // temporary storage for attributes' parameters to avoid double querying inside the loop
        $attributesCache = [];

        foreach ($this->_attrSetColFac->create()->setEntityTypeFilter(
            $this->_entityModel->getEntityTypeId()
        ) as $attributeSet) {
            foreach ($this->_prodAttrColFac->create()->setAttributeSetFilter($attributeSet->getId()) as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $attributeId = $attribute->getId();

                if ($attribute->getIsVisible() || in_array($attributeCode, $this->_forcedAttributesCodes)) {
                    if (!isset($attributesCache[$attributeId])) {
                        $attributesCache[$attributeId] = [
                            'id' => $attributeId,
                            'code' => $attributeCode,
                            'is_global' => $attribute->getIsGlobal(),
                            'is_required' => $attribute->getIsRequired(),
                            'is_unique' => $attribute->getIsUnique(),
                            'frontend_label' => $attribute->getFrontendLabel(),
                            'is_static' => $attribute->isStatic(),
                            'apply_to' => $attribute->getApplyTo(),
                            'type' => \Magento\ImportExport\Model\Import::getAttributeType($attribute),
                            'default_value' => strlen(
                                $attribute->getDefaultValue()
                            ) ? $attribute->getDefaultValue() : null,
                            'options' => $this->_entityModel->getAttributeOptions(
                                $attribute,
                                $this->_indexValueAttributes
                            ),
                        ];
                    }
                    $this->_addAttributeParams(
                        $attributeSet->getAttributeSetName(),
                        $attributesCache[$attributeId],
                        $attribute
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Have we check attribute for is_required? Used as last chance to disable this type of check.
     *
     * @param string $attrCode
     * @return bool
     */
    protected function _isAttributeRequiredCheckNeeded($attrCode)
    {
        return true;
    }

    /**
     * Validate particular attributes columns.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isParticularAttributesValid(array $rowData, $rowNum)
    {
        return true;
    }

    /**
     * Check price correction value validity (signed integer or float with or without percentage sign).
     *
     * @param string $value
     * @return int
     */
    protected function _isPriceCorr($value)
    {
        return preg_match('/^-?\d+\.?\d*%?$/', $value);
    }

    /**
     * Particular attribute names getter.
     *
     * @return string[]
     */
    public function getParticularAttributes()
    {
        return $this->_specialAttributes;
    }

    /**
     * Validate row attributes. Pass VALID row data ONLY as argument.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct Optional
     * @return bool
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $error = false;
        $rowScope = $this->_entityModel->getRowScope($rowData);

        if (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_NULL != $rowScope) {
            foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
                // check value for non-empty in the case of required attribute?
                if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                    $error |= !$this->_entityModel->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
                } elseif ($this->_isAttributeRequiredCheckNeeded($attrCode) && $attrParams['is_required']) {
                    // For the default scope - if this is a new product or
                    // for an old product, if the imported doc has the column present for the attrCode
                    if (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT == $rowScope &&
                        ($isNewProduct ||
                        array_key_exists(
                            $attrCode,
                            $rowData
                        ))
                    ) {
                        $this->_entityModel->addRowError(
                            \Magento\CatalogImportExport\Model\Import\Product::ERROR_VALUE_IS_REQUIRED,
                            $rowNum,
                            $attrCode
                        );
                        $error = true;
                    }
                }
            }
        }
        $error |= !$this->_isParticularAttributesValid($rowData, $rowNum);

        return !$error;
    }

    /**
     * Additional check for model availability. If method returns FALSE - model is not suitable for data processing.
     *
     * @return bool
     */
    public function isSuitable()
    {
        return true;
    }

    /**
     * Prepare attributes values for save: exclude non-existent, static or with empty values attributes;
     * set default values if needed
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     *
     * @return array
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = [];

        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if (!$attrParams['is_static']) {
                if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                    $resultAttrs[$attrCode] = 'select' == $attrParams['type'] ||
                        'multiselect' == $attrParams['type'] ? $attrParams['options'][strtolower(
                            $rowData[$attrCode]
                        )] : $rowData[$attrCode];
                } elseif (array_key_exists($attrCode, $rowData)) {
                    $resultAttrs[$attrCode] = $rowData[$attrCode];
                } elseif ($withDefaultValue && null !== $attrParams['default_value']) {
                    $resultAttrs[$attrCode] = $attrParams['default_value'];
                }
            }
        }

        return $resultAttrs;
    }

    /**
     * Clear empty columns in the Row Data
     *
     * @param array $rowData
     * @return array
     */
    public function clearEmptyData(array $rowData)
    {
        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if (!$attrParams['is_static'] && empty($rowData[$attrCode])) {
                unset($rowData[$attrCode]);
            }
        }
        return $rowData;
    }

    /**
     * Save product type specific data.
     *
     * @return $this
     */
    public function saveData()
    {
        return $this;
    }
}
