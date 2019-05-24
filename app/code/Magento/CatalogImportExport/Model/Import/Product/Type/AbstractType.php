<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Type;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Import entity abstract product type model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractType
{
    /**
     * Common attributes cache
     *
     * @var array
     */
    public static $commonAttributesCache = [];

    /**
     * Maintain a list of invisible attributes
     *
     * @var array
     */
    public static $invAttributesCache = [];

    /**
     * Attribute Code to Id cache
     *
     * @var array
     */
    public static $attributeCodeToId = [];

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
     * Validation failure entity specific message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [];

    /**
     * Validation failure general message template definitions
     *
     * @var array
     */
    protected $_genericMessageTemplates = [
        RowValidatorInterface::ERROR_INVALID_WEIGHT => 'Weight value is incorrect',
        RowValidatorInterface::ERROR_INVALID_WEBSITE => 'Provided Website code doesn\'t exist'
    ];

    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_specialAttributes = [];

    /**
     * Custom entity type fields mapping.
     *
     * @var string[]
     */
    protected $_customFieldsMapping = [];

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
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_attrSetColFac;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $_prodAttrColFac;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 100.1.0
     */
    protected $metadataPool;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * AbstractType constructor
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param ResourceConnection $resource
     * @param array $params
     * @param MetadataPool|null $metadataPool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        MetadataPool $metadataPool = null
    ) {
        $this->_attrSetColFac = $attrSetColFac;
        $this->_prodAttrColFac = $prodAttrColFac;
        $this->_resource = $resource;
        $this->connection = $resource->getConnection();
        $this->metadataPool = $metadataPool ?: $this->getMetadataPool();
        if ($this->isSuitable()) {
            if (!isset($params[0])
                || !isset($params[1])
                || !is_object($params[0])
                || !$params[0] instanceof \Magento\CatalogImportExport\Model\Import\Product
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the parameters.'));
            }
            $this->_entityModel = $params[0];
            $this->_type = $params[1];

            $this->initMessageTemplates(
                array_merge($this->_genericMessageTemplates, $this->_messageTemplates)
            );

            $this->_initAttributes();
        }
    }

    /**
     * Initialize template for error message.
     *
     * @param array $templateCollection
     * @return $this
     */
    protected function initMessageTemplates(array $templateCollection)
    {
        foreach ($templateCollection as $errorCode => $message) {
            $this->_entityModel->addMessageTemplate($errorCode, $message);
        }

        return $this;
    }

    /**
     * Add attribute parameters to appropriate attribute set.
     *
     * @param string $attrSetName Name of attribute set.
     * @param array $attrParams Refined attribute parameters.
     * @param mixed $attribute
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _addAttributeParams($attrSetName, array $attrParams, $attribute)
    {
        if (!$attrParams['apply_to'] || in_array($this->_type, $attrParams['apply_to'])) {
            $this->_attributes[$attrSetName][$attrParams['code']] = $attrParams;
        }
        return $this;
    }

    /**
     * Retrieve product Attribute
     *
     * @param string $attributeCode
     * @param string $attributeSet
     * @return array
     */
    public function retrieveAttribute($attributeCode, $attributeSet)
    {
        if (isset($this->_attributes[$attributeSet]) && isset($this->_attributes[$attributeSet][$attributeCode])) {
            return $this->_attributes[$attributeSet][$attributeCode];
        }
        return [];
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
        $entityId = $this->_entityModel->getEntityTypeId();
        $entityAttributes = $this->connection->fetchAll(
            $this->connection->select()->from(
                ['attr' => $this->_resource->getTableName('eav_entity_attribute')],
                ['attr.attribute_id']
            )->joinLeft(
                ['set' => $this->_resource->getTableName('eav_attribute_set')],
                'set.attribute_set_id = attr.attribute_set_id',
                ['set.attribute_set_name']
            )->where(
                $this->connection->quoteInto('attr.entity_type_id IN (?)', $entityId)
            )
        );
        $absentKeys = [];
        foreach ($entityAttributes as $attributeRow) {
            if (!isset(self::$commonAttributesCache[$attributeRow['attribute_id']])) {
                if (!isset($absentKeys[$attributeRow['attribute_set_name']])) {
                    $absentKeys[$attributeRow['attribute_set_name']] = [];
                }
                $absentKeys[$attributeRow['attribute_set_name']][] = $attributeRow['attribute_id'];
            }
        }
        foreach ($absentKeys as $attributeSetName => $attributeIds) {
            $unknownAttributeIds = array_diff(
                $attributeIds,
                array_keys(self::$commonAttributesCache),
                self::$invAttributesCache
            );
            if ($unknownAttributeIds || $this->_forcedAttributesCodes) {
                $this->attachAttributesById($attributeSetName, $attributeIds);
            }
        }
        foreach ($entityAttributes as $attributeRow) {
            if (isset(self::$commonAttributesCache[$attributeRow['attribute_id']])) {
                $attribute = self::$commonAttributesCache[$attributeRow['attribute_id']];
                $this->_addAttributeParams(
                    $attributeRow['attribute_set_name'],
                    self::$commonAttributesCache[$attributeRow['attribute_id']],
                    $attribute
                );
            }
        }
        return $this;
    }

    /**
     * Attach Attributes By Id
     *
     * @param string $attributeSetName
     * @param array $attributeIds
     * @return void
     */
    protected function attachAttributesById($attributeSetName, $attributeIds)
    {
        foreach ($this->_prodAttrColFac->create()->addFieldToFilter(
            ['main_table.attribute_id', 'main_table.attribute_code'],
            [
                ['in' => $attributeIds],
                ['in' => $this->_forcedAttributesCodes]
            ]
        ) as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeId = $attribute->getId();

            if ($attribute->getIsVisible() || in_array($attributeCode, $this->_forcedAttributesCodes)) {
                if (!isset(self::$commonAttributesCache[$attributeId])) {
                    self::$commonAttributesCache[$attributeId] = [
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

                self::$attributeCodeToId[$attributeCode] = $attributeId;
                $this->_addAttributeParams(
                    $attributeSetName,
                    self::$commonAttributesCache[$attributeId],
                    $attribute
                );
            } else {
                self::$invAttributesCache[] = $attributeId;
            }
        }
    }

    /**
     * Retrieve attribute from cache
     *
     * @param string $attributeCode
     * @return mixed
     */
    public function retrieveAttributeFromCache($attributeCode)
    {
        if (isset(self::$attributeCodeToId[$attributeCode]) && $id = self::$attributeCodeToId[$attributeCode]) {
            if (isset(self::$commonAttributesCache[$id])) {
                return self::$commonAttributesCache[$id];
            }
        }
        return [];
    }

    /**
     * Adding attribute option.
     *
     * In case we've dynamically added new attribute option during import we need to add it to our cache
     * in order to keep it up to date.
     *
     * @param string $code
     * @param string $optionKey
     * @param string $optionValue
     *
     * @return $this
     */
    public function addAttributeOption($code, $optionKey, $optionValue)
    {
        foreach ($this->_attributes as $attrSetName => $attrSetValue) {
            if (isset($attrSetValue[$code])) {
                $this->_attributes[$attrSetName][$code]['options'][$optionKey] = $optionValue;
            }
        }
        return $this;
    }

    /**
     * Have we check attribute for is_required? Used as last chance to disable this type of check.
     *
     * @param string $attrCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * Return entity custom Fields mapping.
     *
     * @return string[]
     */
    public function getCustomFieldsMapping()
    {
        return $this->_customFieldsMapping;
    }

    /**
     * Validate row attributes. Pass VALID row data ONLY as argument.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct Optional
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $error = false;
        $rowScope = $this->_entityModel->getRowScope($rowData);
        if (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_NULL != $rowScope
            && !empty($rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU])
        ) {
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
                            RowValidatorInterface::ERROR_VALUE_IS_REQUIRED,
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
     * Adding default attribute to product before save.
     *
     * Prepare attributes values for save: exclude non-existent, static or with empty values attributes,
     * set default values if needed.
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = [];

        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if ($attrParams['is_static']) {
                continue;
            }
            if (isset($rowData[$attrCode]) && strlen(trim($rowData[$attrCode]))) {
                if (in_array($attrParams['type'], ['select', 'boolean'])) {
                    $resultAttrs[$attrCode] = $attrParams['options'][strtolower($rowData[$attrCode])];
                } elseif ('multiselect' == $attrParams['type']) {
                    $resultAttrs[$attrCode] = [];
                    foreach ($this->_entityModel->parseMultiselectValues($rowData[$attrCode]) as $value) {
                        $resultAttrs[$attrCode][] = $attrParams['options'][strtolower($value)];
                    }
                    $resultAttrs[$attrCode] = implode(',', $resultAttrs[$attrCode]);
                } else {
                    $resultAttrs[$attrCode] = $rowData[$attrCode];
                }
            } elseif (array_key_exists($attrCode, $rowData) && empty($rowData['_store'])) {
                $resultAttrs[$attrCode] = $rowData[$attrCode];
            } elseif ($withDefaultValue && null !== $attrParams['default_value'] && empty($rowData['_store'])) {
                $resultAttrs[$attrCode] = $attrParams['default_value'];
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
            if (!$attrParams['is_static'] && !isset($rowData[$attrCode])) {
                unset($rowData[$attrCode]);
            }

            if (isset($rowData[$attrCode])
                && $rowData[$attrCode] === $this->_entityModel->getEmptyAttributeValueConstant()
            ) {
                $rowData[$attrCode] = null;
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

    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 100.1.0
     */
    protected function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Get product entity link field
     *
     * @return string
     * @since 100.1.0
     */
    protected function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Clean cached values.
     *
     * @since 100.2.0
     */
    public function __destruct()
    {
        self::$attributeCodeToId = [];
        self::$commonAttributesCache = [];
    }
}
