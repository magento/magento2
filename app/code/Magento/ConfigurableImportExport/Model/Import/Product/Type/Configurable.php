<?php
/**
 * Import entity configurable product type model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableImportExport\Model\Import\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Importing configurable products
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Error codes.
     */
    const ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER = 'attrCodeIsNotSuper';

    const ERROR_INVALID_OPTION_VALUE = 'invalidOptionValue';

    const ERROR_INVALID_WEBSITE = 'invalidSuperAttrWebsite';

    const ERROR_DUPLICATED_VARIATIONS = 'duplicatedVariations';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER => 'Attribute with code "%s" is not super',
        self::ERROR_INVALID_OPTION_VALUE => 'Invalid option value for attribute "%s"',
        self::ERROR_INVALID_WEBSITE => 'Invalid website code for super attribute',
        self::ERROR_DUPLICATED_VARIATIONS => 'SKU %s contains duplicated variations',
    ];

    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_specialAttributes = [
        '_super_products_sku',
        '_super_attribute_code',
        '_super_attribute_option',
        '_super_attribute_price_website',
    ];

    /**
     * Reference array of existing product-attribute to product super attribute ID.
     *
     * Example: product_1 (underscore) attribute_id_1 => product_super_attr_id_1,
     * product_1 (underscore) attribute_id_2 => product_super_attr_id_2,
     * ...,
     * product_n (underscore) attribute_id_n => product_super_attr_id_n
     *
     * @var array
     */
    protected $_productSuperAttrs = [];

    /**
     * Array of SKU to array of super attribute values for all products.
     *
     * array (
     *     attr_set_name_1 => array(
     *         product_id_1 => array(
     *             super_attribute_code_1 => attr_value_1,
     *             ...
     *             super_attribute_code_n => attr_value_n
     *         ),
     *         ...
     *     ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_skuSuperAttributeValues = [];

    /**
     * Array of SKU to array of super attributes data for validation new associated products.
     *
     * array (
     *     product_id_1 => array(
     *         super_attribute_id_1 => array(
     *             value_index_1 => TRUE,
     *             ...
     *             value_index_n => TRUE
     *         ),
     *         ...
     *     ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_skuSuperData = [];

    /**
     * Super attributes codes in a form of code => TRUE array pairs.
     *
     * @var array
     */
    protected $_superAttributes = [];

    /**
     * All super attributes values combinations for each attribute set.
     *
     * @var array
     */
    protected $_superAttrValuesCombs = null;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $_productTypesConfig;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Instance of database adapter.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated
     */
    protected $_connection;

    /**
     * Instance of product collection factory.
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productColFac;

    /**
     * Product data.
     *
     * @var array
     */
    protected $_productData;

    /**
     * Product super data.
     *
     * @var array
     */
    protected $_productSuperData;

    /**
     * Simple product ids to delete.
     *
     * @var array
     */
    protected $_simpleIdsToDelete;

    /**
     * Super attributes data.
     *
     * @var array
     */
    protected $_superAttributesData;

    /**
     * Next attribute id.
     *
     * @var null|int
     */
    protected $_nextAttrId;

    /**
     * Product entity identifier field
     *
     * @var string
     */
    private $productEntityIdentifierField;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $params
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypesConfig
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productColFac
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypesConfig,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productColFac,
        MetadataPool $metadataPool = null
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $metadataPool);
        $this->_productTypesConfig = $productTypesConfig;
        $this->_resourceHelper = $resourceHelper;
        $this->_productColFac = $_productColFac;
        $this->_connection = $this->_entityModel->getConnection();
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
        // save super attributes for simplier and quicker search in future
        if ('select' == $attrParams['type'] && 1 == $attrParams['is_global']) {
            $this->_superAttributes[$attrParams['code']] = $attrParams;
        }
        return parent::_addAttributeParams($attrSetName, $attrParams, $attribute);
    }

    /**
     * Get super attribute ID (if it is not possible - return NULL).
     *
     * @param int $productId
     * @param int $attributeId
     * @return array|null
     */
    protected function _getSuperAttributeId($productId, $attributeId)
    {
        if (isset($this->_productSuperAttrs["{$productId}_{$attributeId}"])) {
            return $this->_productSuperAttrs["{$productId}_{$attributeId}"];
        } else {
            return null;
        }
    }

    /**
     * Have we check attribute for is_required? Used as last chance to disable this type of check.
     *
     * @param string $attrCode
     * @return bool
     */
    protected function _isAttributeRequiredCheckNeeded($attrCode)
    {
        // do not check super attributes
        return !$this->_isAttributeSuper($attrCode);
    }

    /**
     * Is attribute is super-attribute?
     *
     * @param string $attrCode
     * @return bool
     */
    protected function _isAttributeSuper($attrCode)
    {
        return isset($this->_superAttributes[$attrCode]);
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
        if (!empty($rowData['_super_attribute_code'])) {
            $superAttrCode = $rowData['_super_attribute_code'];

            if (!$this->_isAttributeSuper($superAttrCode)) {
                // check attribute superity
                $this->_entityModel->addRowError(self::ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER, $rowNum, $superAttrCode);
                return false;
            } elseif (isset($rowData['_super_attribute_option']) && strlen($rowData['_super_attribute_option'])) {
                $optionKey = strtolower($rowData['_super_attribute_option']);
                if (!isset($this->_superAttributes[$superAttrCode]['options'][$optionKey])) {
                    $this->_entityModel->addRowError(self::ERROR_INVALID_OPTION_VALUE, $rowNum, $superAttrCode);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Array of SKU to array of super attribute values for all products.
     *
     * @param array $bunch - portion of products to process
     * @param array $newSku - imported variations list
     * @param array $oldSku - present variations list
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _loadSkuSuperAttributeValues($bunch, $newSku, $oldSku)
    {
        if ($this->_superAttributes) {
            $attrSetIdToName = $this->_entityModel->getAttrSetIdToName();

            $productIds = [];
            foreach ($bunch as $rowData) {
                $dataWithExtraVirtualRows = $this->_parseVariations($rowData);
                if (!empty($dataWithExtraVirtualRows)) {
                    array_unshift($dataWithExtraVirtualRows, $rowData);
                } else {
                    $dataWithExtraVirtualRows[] = $rowData;
                }

                foreach ($dataWithExtraVirtualRows as $data) {
                    if (!empty($data['_super_products_sku'])) {
                        if (isset($newSku[$data['_super_products_sku']])) {
                            $productIds[] = $newSku[$data['_super_products_sku']][$this->getProductEntityLinkField()];
                        } elseif (isset($oldSku[$data['_super_products_sku']])) {
                            $productIds[] = $oldSku[$data['_super_products_sku']][$this->getProductEntityLinkField()];
                        }
                    }
                }
            }

            foreach ($this->_productColFac->create()->addFieldToFilter(
                'type_id',
                $this->_productTypesConfig->getComposableTypes()
            )->addFieldToFilter(
                $this->getProductEntityLinkField(),
                ['in' => $productIds]
            )->addAttributeToSelect(
                array_keys($this->_superAttributes)
            ) as $product) {
                $attrSetName = $attrSetIdToName[$product->getAttributeSetId()];

                $data = array_intersect_key($product->getData(), $this->_superAttributes);
                foreach ($data as $attrCode => $value) {
                    $attrId = $this->_superAttributes[$attrCode]['id'];
                    $productId = $product->getData($this->getProductEntityLinkField());
                    $this->_skuSuperAttributeValues[$attrSetName][$productId][$attrId] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Array of SKU to array of super attribute values for all products.
     *
     * @param array $bunch
     * @return $this
     */
    protected function _loadSkuSuperDataForBunch(array $bunch)
    {
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        $productIds = [];
        foreach ($bunch as $rowData) {
            $sku = strtolower($rowData[ImportProduct::COL_SKU]);
            $productData = isset($newSku[$sku]) ? $newSku[$sku] : $oldSku[$sku];
            $productIds[] = $productData[$this->getProductEntityLinkField()];
        }

        $this->_productSuperAttrs = [];
        $this->_skuSuperData = [];
        if (!empty($productIds)) {
            $mainTable = $this->_resource->getTableName('catalog_product_super_attribute');
            $optionTable = $this->_resource->getTableName('eav_attribute_option');
            $select = $this->connection->select()->from(
                ['m' => $mainTable],
                ['product_id', 'attribute_id', 'product_super_attribute_id']
            )->joinLeft(
                ['o' => $optionTable],
                $this->connection->quoteIdentifier(
                    'm.attribute_id'
                ) . ' = ' . $this->connection->quoteIdentifier(
                    'o.attribute_id'
                ),
                ['option_id']
            )->where(
                'm.product_id IN ( ? )',
                $productIds
            );

            foreach ($this->connection->fetchAll($select) as $row) {
                $attrId = $row['attribute_id'];
                $productId = $row['product_id'];
                if ($row['option_id']) {
                    $this->_skuSuperData[$productId][$attrId][$row['option_id']] = true;
                }
                $this->_productSuperAttrs["{$productId}_{$attrId}"] = $row['product_super_attribute_id'];
            }
        }
        return $this;
    }

    /**
     * Validate and prepare data about super attributes and associated products.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _processSuperData()
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        if ($this->_productSuperData) {
            $usedCombs = [];
            // is associated products applicable?
            foreach (array_keys($this->_productSuperData['assoc_ids']) as $assocId) {
                if (!isset($this->_skuSuperAttributeValues[$this->_productSuperData['attr_set_code']][$assocId])) {
                    continue;
                }
                if ($this->_productSuperData['used_attributes']) {
                    $skuSuperValues = $this
                        ->_skuSuperAttributeValues[$this->_productSuperData['attr_set_code']][$assocId];
                    $usedCombParts = [];

                    foreach ($this->_productSuperData['used_attributes'] as $usedAttrId => $usedValues) {
                        if (empty($skuSuperValues[$usedAttrId]) || !isset($usedValues[$skuSuperValues[$usedAttrId]])) {
                            // invalid value or value does not exists for associated product
                            continue;
                        }
                        $usedCombParts[] = $skuSuperValues[$usedAttrId];
                        $this->_productSuperData['used_attributes'][$usedAttrId][$skuSuperValues[$usedAttrId]] = true;
                    }
                    $comb = implode('|', $usedCombParts);

                    if (isset($usedCombs[$comb])) {
                        // super attributes values combination was already used
                        continue;
                    }
                    $usedCombs[$comb] = true;
                }
                $this->_superAttributesData['super_link'][] = [
                    'product_id' => $this->_productSuperData['assoc_entity_ids'][$assocId],
                    'parent_id' => $this->_productSuperData['product_id'],
                ];
                $subEntityId = $this->connection->fetchOne(
                    $this->connection->select()->from(
                        ['cpe' => $this->_resource->getTableName('catalog_product_entity')], ['entity_id']
                    )->where($metadata->getLinkField() . ' = ?', $assocId)
                );
                $this->_superAttributesData['relation'][] = [
                    'parent_id' => $this->_productSuperData['product_id'],
                    'child_id' => $subEntityId,
                ];
            }
        }
        return $this;
    }

    /**
     * Parse variations string to inner format.
     *
     * @param array $rowData
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseVariations($rowData)
    {
        $additionalRows = [];
        if (!isset($rowData['configurable_variations'])) {
            return $additionalRows;
        }
        $variations = explode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $rowData['configurable_variations']);
        foreach ($variations as $variation) {
            $fieldAndValuePairsText = explode($this->_entityModel->getMultipleValueSeparator(), $variation);
            $additionalRow = [];

            $fieldAndValuePairs = [];
            foreach ($fieldAndValuePairsText as $nameAndValue) {
                $nameAndValue = explode(ImportProduct::PAIR_NAME_VALUE_SEPARATOR, $nameAndValue);
                if (!empty($nameAndValue)) {
                    $value = isset($nameAndValue[1]) ? trim($nameAndValue[1]) : '';
                    $fieldName  = trim($nameAndValue[0]);
                    if ($fieldName) {
                        $fieldAndValuePairs[$fieldName] = $value;
                    }
                }
            }

            if (!empty($fieldAndValuePairs['sku'])) {
                $position = 0;
                $additionalRow['_super_products_sku'] = strtolower($fieldAndValuePairs['sku']);
                unset($fieldAndValuePairs['sku']);
                $additionalRow['display'] = isset($fieldAndValuePairs['display']) ? $fieldAndValuePairs['display'] : 1;
                unset($fieldAndValuePairs['display']);
                foreach ($fieldAndValuePairs as $attrCode => $attrValue) {
                    $additionalRow['_super_attribute_code'] = $attrCode;
                    $additionalRow['_super_attribute_option'] = $attrValue;
                    $additionalRow['_super_attribute_position'] = $position;
                    $additionalRows[] = $additionalRow;
                    $additionalRow = [];
                    $position += 1;
                }
            }
        }
        return $additionalRows;
    }

    /**
     * Parse variation labels to array
     *  ...attribute_code => label ...
     *  ...attribute_code2 => label2 ...
     *
     * @param array $rowData
     *
     * @return array
     */
    protected function _parseVariationLabels($rowData)
    {
        $labels = [];
        if (!isset($rowData['configurable_variation_labels'])) {
            return $labels;
        }
        $pairFieldAndValue = explode(
            $this->_entityModel->getMultipleValueSeparator(),
            $rowData['configurable_variation_labels']
        );

        foreach ($pairFieldAndValue as $nameAndValue) {
            $nameAndValue = explode(ImportProduct::PAIR_NAME_VALUE_SEPARATOR, $nameAndValue);
            if (!empty($nameAndValue)) {
                $value = isset($nameAndValue[1]) ? trim($nameAndValue[1]) : '';
                $attrCode  = trim($nameAndValue[0]);
                if ($attrCode) {
                    $labels[$attrCode] = $value;
                }
            }
        }
        return $labels;
    }

    /**
     * Delete unnecessary links.
     *
     * @return $this
     */
    protected function _deleteData()
    {
        $linkTable = $this->_resource->getTableName('catalog_product_super_link');
        $relationTable = $this->_resource->getTableName('catalog_product_relation');

        if (($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND)
            && !empty($this->_productSuperData['product_id'])
            && !empty($this->_simpleIdsToDelete)
        ) {
            $quoted = $this->connection->quoteInto('IN (?)', [$this->_productSuperData['product_id']]);
            $quotedChildren = $this->connection->quoteInto('IN (?)', $this->_simpleIdsToDelete);
            $this->connection->delete($linkTable, "parent_id {$quoted} AND product_id {$quotedChildren}");
            $this->connection->delete($relationTable, "parent_id {$quoted} AND child_id {$quotedChildren}");
        }
        return $this;
    }

    /**
     *  Collected link data insertion.
     *
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function _insertData()
    {
        $mainTable = $this->_resource->getTableName('catalog_product_super_attribute');
        $labelTable = $this->_resource->getTableName('catalog_product_super_attribute_label');
        $linkTable = $this->_resource->getTableName('catalog_product_super_link');
        $relationTable = $this->_resource->getTableName('catalog_product_relation');

        $mainData = [];
        foreach ($this->_superAttributesData['attributes'] as $productId => $attributesData) {
            foreach ($attributesData as $attrId => $row) {
                $row['product_id'] = $productId;
                $row['attribute_id'] = $attrId;
                $mainData[] = $row;
            }
        }
        if ($mainData) {
            $this->connection->insertOnDuplicate($mainTable, $mainData);
        }
        if ($this->_superAttributesData['labels']) {
            $this->connection->insertOnDuplicate($labelTable, $this->_superAttributesData['labels']);
        }
        if ($this->_superAttributesData['super_link']) {
            $this->connection->insertOnDuplicate($linkTable, $this->_superAttributesData['super_link']);
        }
        if ($this->_superAttributesData['relation']) {
            $this->connection->insertOnDuplicate($relationTable, $this->_superAttributesData['relation']);
        }
        return $this;
    }

    /**
     * Get new supper attribute id.
     *
     * @return int
     */
    protected function _getNextAttrId()
    {
        if (!$this->_nextAttrId) {
            $mainTable = $this->_resource->getTableName('catalog_product_super_attribute');
            $this->_nextAttrId = $this->_resourceHelper->getNextAutoincrement($mainTable);
        }
        $this->_nextAttrId++;
        return $this->_nextAttrId;
    }

    /**
     *  Collect super data.
     *
     * @param array $rowData
     * @return $this
     */
    protected function _collectSuperData($rowData)
    {
        $entityId = $this->_productData[$this->getProductEntityIdentifierField()];
        $linkId = $this->_productData[$this->getProductEntityLinkField()];

        $this->_processSuperData();

        $this->_productSuperData = [
            'product_id' => $linkId,
            'entity_id' => $entityId,
            'attr_set_code' => $this->_productData['attr_set_code'],
            'used_attributes' => empty($this->_skuSuperData[$linkId]) ? [] : $this->_skuSuperData[$linkId],
            'assoc_ids' => [],
        ];

        $additionalRows = $this->_parseVariations($rowData);
        $variationLabels = $this->_parseVariationLabels($rowData);
        //@codingStandardsIgnoreStart
        foreach ($additionalRows as $data) {
            $this->_collectAssocIds($data);

            if (!isset($this->_superAttributes[$data['_super_attribute_code']])) {
                continue;
            }
            $attrParams = $this->_superAttributes[$data['_super_attribute_code']];

            // @todo understand why do we need this condition
            if ($this->_getSuperAttributeId($linkId, $attrParams['id'])) {
                $productSuperAttrId = $this->_getSuperAttributeId($linkId, $attrParams['id']);
            } elseif (isset($this->_superAttributesData['attributes'][$linkId][$attrParams['id']])) {
                $attributes = $this->_superAttributesData['attributes'];
                $productSuperAttrId = $attributes[$linkId][$attrParams['id']]['product_super_attribute_id'];
                $this->_collectSuperDataLabels($data, $productSuperAttrId, $linkId, $variationLabels);
            } else {
                $productSuperAttrId = $this->_getNextAttrId();
                $this->_collectSuperDataLabels($data, $productSuperAttrId, $linkId, $variationLabels);
            }
        }
        //@codingStandardsIgnoreEnd

        return $this;
    }

    /**
     *  Collect assoc ids and simpleIds to break links.
     *
     * @param array $data
     * @return $this
     */
    protected function _collectAssocIds($data)
    {
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        if (!empty($data['_super_products_sku'])) {
            if (isset($newSku[$data['_super_products_sku']])) {
                $superProductRowId = $newSku[$data['_super_products_sku']][$this->getProductEntityLinkField()];
                $superProductEntityId = $newSku[$data['_super_products_sku']][$this->getProductEntityIdentifierField()];
            } elseif (isset($oldSku[$data['_super_products_sku']])) {
                $superProductRowId = $oldSku[$data['_super_products_sku']][$this->getProductEntityLinkField()];
                $superProductEntityId = $oldSku[$data['_super_products_sku']][$this->getProductEntityIdentifierField()];
            }
            if (isset($superProductRowId)) {
                if (isset($data['display']) && $data['display'] == 0) {
                    $this->_simpleIdsToDelete[] = $superProductRowId;
                } else {
                    $this->_productSuperData['assoc_ids'][$superProductRowId] = true;
                    $this->_productSuperData['assoc_entity_ids'][$superProductRowId] = $superProductEntityId;
                }
            }
        }
        return $this;
    }

    /**
     *  Collect super data labels.
     *
     * @param array $data
     * @param integer|string $productSuperAttrId
     * @param integer|string $productId
     * @param array $variationLabels
     * @return $this
     */
    protected function _collectSuperDataLabels($data, $productSuperAttrId, $productId, $variationLabels)
    {
        $attrParams = $this->_superAttributes[$data['_super_attribute_code']];
        $this->_superAttributesData['attributes'][$productId][$attrParams['id']] = [
            'product_super_attribute_id' => $productSuperAttrId,
            'position' => $data['_super_attribute_position'],
        ];
        $label = isset($variationLabels[$data['_super_attribute_code']])
                ? $variationLabels[$data['_super_attribute_code']]
                : $attrParams['frontend_label'];
        $this->_superAttributesData['labels'][$productSuperAttrId] = [
            'product_super_attribute_id' => $productSuperAttrId,
            'store_id' => 0,
            'use_default' => $label ? 0 : 1,
            'value' => $label,
        ];
        return $this;
    }

    /**
     * Save product type specific data.
     *
     * @throws \Exception
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveData()
    {
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        $this->_productSuperData = [];
        $this->_productData = null;

        while ($bunch = $this->_entityModel->getNextBunch()) {
            if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
                $this->_loadSkuSuperDataForBunch($bunch);
            }
            if (!$this->configurableInBunch($bunch)) {
                continue;
            }

            $this->_superAttributesData = [
                'attributes' => [],
                'labels' => [],
                'super_link' => [],
                'relation' => [],
            ];

            $this->_simpleIdsToDelete = [];

            $this->_loadSkuSuperAttributeValues($bunch, $newSku, $oldSku);

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                // remember SCOPE_DEFAULT row data
                $scope = $this->_entityModel->getRowScope($rowData);
                if (ImportProduct::SCOPE_DEFAULT == $scope &&
                    !empty($rowData[ImportProduct::COL_SKU])) {
                    $sku = strtolower($rowData[ImportProduct::COL_SKU]);
                    $this->_productData = isset($newSku[$sku]) ? $newSku[$sku] : $oldSku[$sku];

                    if ($this->_type != $this->_productData['type_id']) {
                        $this->_productData = null;
                        continue;
                    }
                    $this->_collectSuperData($rowData);
                }
            }

            // save last product super data
            $this->_processSuperData();

            $this->_deleteData();

            $this->_insertData();
        }
        return $this;
    }

    /**
     * Configurable in bunch
     *
     * @param array $bunch
     * @return bool
     */
    protected function configurableInBunch($bunch)
    {
        $newSku = $this->_entityModel->getNewSku();
        foreach ($bunch as $rowNum => $rowData) {
            $productData = $newSku[strtolower($rowData[ImportProduct::COL_SKU])];
            if (($this->_type == $productData['type_id']) &&
                ($rowData == $this->_entityModel->isRowAllowedToImport($rowData, $rowNum))
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate row attributes. Pass VALID row data ONLY as argument.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct Optional
     *
     * @return bool
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $error = false;
        $dataWithExtraVirtualRows = $this->_parseVariations($rowData);
        $skus = [];
        $rowData['price'] = isset($rowData['price']) && $rowData['price'] ? $rowData['price'] : '0.00';
        if (!empty($dataWithExtraVirtualRows)) {
            array_unshift($dataWithExtraVirtualRows, $rowData);
        } else {
            $dataWithExtraVirtualRows[] = $rowData;
        }
        foreach ($dataWithExtraVirtualRows as $option) {
            if (isset($option['_super_products_sku'])) {
                if (in_array($option['_super_products_sku'], $skus)) {
                    $error = true;
                    $this->_entityModel->addRowError(sprintf($this->_messageTemplates[self::ERROR_DUPLICATED_VARIATIONS], $option['_super_products_sku']), $rowNum);
                }
                $skus[] = $option['_super_products_sku'];
            }
            $error |= !parent::isRowValid($option, $rowNum, $isNewProduct);
        }
        return !$error;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     */
    private function getProductEntityIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }
}
