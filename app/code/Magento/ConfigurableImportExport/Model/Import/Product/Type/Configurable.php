<?php
/**
 * Import entity configurable product type model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Import\Product\Type;

class Configurable extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Error codes.
     */
    const ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER = 'attrCodeIsNotSuper';

    const ERROR_INVALID_PRICE_CORRECTION = 'invalidPriceCorr';

    const ERROR_INVALID_OPTION_VALUE = 'invalidOptionValue';

    const ERROR_INVALID_WEBSITE = 'invalidSuperAttrWebsite';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER => 'Attribute with this code is not super',
        self::ERROR_INVALID_PRICE_CORRECTION => 'Super attribute price correction value is invalid',
        self::ERROR_INVALID_OPTION_VALUE => 'Invalid option value',
        self::ERROR_INVALID_WEBSITE => 'Invalid website code for super attribute',
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
        '_super_attribute_price_corr',
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
     * @var \Magento\ImportExport\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productColFac;

    /**
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param array $params
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypesConfig
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $_productColFac
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac,
        array $params,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypesConfig,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\App\Resource $resource,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $_productColFac
    ) {
        $this->_productTypesConfig = $productTypesConfig;
        $this->_resourceHelper = $resourceHelper;
        $this->_resource = $resource;
        $this->_productColFac = $_productColFac;
        parent::__construct($attrSetColFac, $prodAttrColFac, $params);
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
        if ('select' == $attrParams['type'] && 1 == $attrParams['is_global'] && $attribute->getIsConfigurable()) {
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
                $this->_entityModel->addRowError(self::ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER, $rowNum);
                return false;
            } elseif (isset($rowData['_super_attribute_option']) && strlen($rowData['_super_attribute_option'])) {
                $optionKey = strtolower($rowData['_super_attribute_option']);
                if (!isset($this->_superAttributes[$superAttrCode]['options'][$optionKey])) {
                    $this->_entityModel->addRowError(self::ERROR_INVALID_OPTION_VALUE, $rowNum);
                    return false;
                }
                // check price value
                if (!empty($rowData['_super_attribute_price_corr']) && !$this->_isPriceCorr(
                    $rowData['_super_attribute_price_corr']
                )
                ) {
                    $this->_entityModel->addRowError(self::ERROR_INVALID_PRICE_CORRECTION, $rowNum);
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
     */
    protected function _loadSkuSuperAttributeValues($bunch, $newSku, $oldSku)
    {
        if ($this->_superAttributes) {
            $attrSetIdToName = $this->_entityModel->getAttrSetIdToName();

            $productIds = [];
            foreach ($bunch as $rowData) {
                if (!empty($rowData['_super_products_sku'])) {
                    if (isset($newSku[$rowData['_super_products_sku']])) {
                        $productIds[] = $newSku[$rowData['_super_products_sku']]['entity_id'];
                    } elseif (isset($oldSku[$rowData['_super_products_sku']])) {
                        $productIds[] = $oldSku[$rowData['_super_products_sku']]['entity_id'];
                    }
                }
            }

            foreach ($this->_productColFac->create()->addFieldToFilter(
                'type_id',
                $this->_productTypesConfig->getComposableTypes()
            )->addFieldToFilter(
                'entity_id',
                ['in' => $productIds]
            )->addAttributeToSelect(
                array_keys($this->_superAttributes)
            ) as $product) {
                $attrSetName = $attrSetIdToName[$product->getAttributeSetId()];

                $data = array_intersect_key($product->getData(), $this->_superAttributes);
                foreach ($data as $attrCode => $value) {
                    $attrId = $this->_superAttributes[$attrCode]['id'];
                    $this->_skuSuperAttributeValues[$attrSetName][$product->getId()][$attrId] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Array of SKU to array of super attribute values for all products.
     *
     * @return $this
     */
    protected function _loadSkuSuperData()
    {
        if (!$this->_skuSuperData) {
            $connection = $this->_entityModel->getConnection();
            $mainTable = $this->_resource->getTableName('catalog_product_super_attribute');
            $priceTable = $this->_resource->getTableName('catalog_product_super_attribute_pricing');
            $select = $connection->select()->from(
                ['m' => $mainTable],
                ['product_id', 'attribute_id', 'product_super_attribute_id']
            )->joinLeft(
                ['p' => $priceTable],
                $connection->quoteIdentifier(
                    'p.product_super_attribute_id'
                ) . ' = ' . $connection->quoteIdentifier(
                    'm.product_super_attribute_id'
                ),
                ['value_index']
            );

            foreach ($connection->fetchAll($select) as $row) {
                $attrId = $row['attribute_id'];
                $productId = $row['product_id'];
                if ($row['value_index']) {
                    $this->_skuSuperData[$productId][$attrId][$row['value_index']] = true;
                }
                $this->_productSuperAttrs["{$productId}_{$attrId}"] = $row['product_super_attribute_id'];
            }
        }
        return $this;
    }

    /**
     * Validate and prepare data about super attributes and associated products.
     *
     * @param array $superData
     * @param array $superAttributes
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _processSuperData(array $superData, array &$superAttributes)
    {
        if ($superData) {
            $usedCombs = [];
            // is associated products applicable?
            foreach (array_keys($superData['assoc_ids']) as $assocId) {
                if (!isset($this->_skuSuperAttributeValues[$superData['attr_set_code']][$assocId])) {
                    continue;
                }
                if ($superData['used_attributes']) {
                    $skuSuperValues = $this->_skuSuperAttributeValues[$superData['attr_set_code']][$assocId];
                    $usedCombParts = [];

                    foreach ($superData['used_attributes'] as $usedAttrId => $usedValues) {
                        if (empty($skuSuperValues[$usedAttrId]) || !isset($usedValues[$skuSuperValues[$usedAttrId]])) {
                            // invalid value or value does not exists for associated product
                            continue;
                        }
                        $usedCombParts[] = $skuSuperValues[$usedAttrId];
                        $superData['used_attributes'][$usedAttrId][$skuSuperValues[$usedAttrId]] = true;
                    }
                    $comb = implode('|', $usedCombParts);

                    if (isset($usedCombs[$comb])) {
                        // super attributes values combination was already used
                        continue;
                    }
                    $usedCombs[$comb] = true;
                }
                $superAttributes['super_link'][] = [
                    'product_id' => $assocId,
                    'parent_id' => $superData['product_id'],
                ];
                $superAttributes['relation'][] = [
                    'parent_id' => $superData['product_id'],
                    'child_id' => $assocId,
                ];
            }
            // clean up unused values pricing
            foreach ($superData['used_attributes'] as $usedAttrId => $usedValues) {
                foreach ($usedValues as $optionId => $isUsed) {
                    if (!$isUsed && isset($superAttributes['pricing'][$superData['product_id']][$usedAttrId])) {
                        foreach ($superAttributes['pricing'][$superData['product_id']][$usedAttrId] as $k => $params) {
                            if ($optionId == $params['value_index']) {
                                unset($superAttributes['pricing'][$superData['product_id']][$usedAttrId][$k]);
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Save product type specific data.
     *
     * @throws \Exception
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function saveData()
    {
        $connection = $this->_entityModel->getConnection();
        $mainTable = $this->_resource->getTableName('catalog_product_super_attribute');
        $labelTable = $this->_resource->getTableName('catalog_product_super_attribute_label');
        $priceTable = $this->_resource->getTableName('catalog_product_super_attribute_pricing');
        $linkTable = $this->_resource->getTableName('catalog_product_super_link');
        $relationTable = $this->_resource->getTableName('catalog_product_relation');
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        $productSuperData = [];
        $productData = null;
        $nextAttrId = $this->_resourceHelper->getNextAutoincrement($mainTable);

        if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
            $this->_loadSkuSuperData();
        }

        while ($bunch = $this->_entityModel->getNextBunch()) {
            $superAttributes = [
                'attributes' => [],
                'labels' => [],
                'pricing' => [],
                'super_link' => [],
                'relation' => [],
            ];

            $this->_loadSkuSuperAttributeValues($bunch, $newSku, $oldSku);

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                // remember SCOPE_DEFAULT row data
                $scope = $this->_entityModel->getRowScope($rowData);
                if (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT == $scope) {
                    $productData = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]];

                    if ($this->_type != $productData['type_id']) {
                        $productData = null;
                        continue;
                    }
                    $productId = $productData['entity_id'];

                    $this->_processSuperData($productSuperData, $superAttributes);

                    $productSuperData = [
                        'product_id' => $productId,
                        'attr_set_code' => $productData['attr_set_code'],
                        'used_attributes' => empty($this->_skuSuperData[$productId]) ? [] : $this
                            ->_skuSuperData[$productId],
                        'assoc_ids' => [],
                    ];
                } elseif (null === $productData) {
                    continue;
                }
                if (!empty($rowData['_super_products_sku'])) {
                    if (isset($newSku[$rowData['_super_products_sku']])) {
                        $productSuperData['assoc_ids'][$newSku[$rowData['_super_products_sku']]['entity_id']] = true;
                    } elseif (isset($oldSku[$rowData['_super_products_sku']])) {
                        $productSuperData['assoc_ids'][$oldSku[$rowData['_super_products_sku']]['entity_id']] = true;
                    }
                }
                if (empty($rowData['_super_attribute_code'])) {
                    continue;
                }
                $attrParams = $this->_superAttributes[$rowData['_super_attribute_code']];

                if ($this->_getSuperAttributeId($productId, $attrParams['id'])) {
                    $productSuperAttrId = $this->_getSuperAttributeId($productId, $attrParams['id']);
                } elseif (!isset($superAttributes['attributes'][$productId][$attrParams['id']])) {
                    $productSuperAttrId = $nextAttrId++;
                    $superAttributes['attributes'][$productId][$attrParams['id']] = [
                        'product_super_attribute_id' => $productSuperAttrId,
                        'position' => 0,
                    ];
                    $superAttributes['labels'][] = [
                        'product_super_attribute_id' => $productSuperAttrId,
                        'store_id' => 0,
                        'use_default' => 1,
                        'value' => $attrParams['frontend_label'],
                    ];
                }
                if (isset($rowData['_super_attribute_option']) && strlen($rowData['_super_attribute_option'])) {
                    $optionId = $attrParams['options'][strtolower($rowData['_super_attribute_option'])];

                    if (!isset($productSuperData['used_attributes'][$attrParams['id']][$optionId])) {
                        $productSuperData['used_attributes'][$attrParams['id']][$optionId] = false;
                    }
                    if (!empty($rowData['_super_attribute_price_corr'])) {
                        $superAttributes['pricing'][] = [
                            'product_super_attribute_id' => $productSuperAttrId,
                            'value_index' => $optionId,
                            'is_percent' => '%' == substr($rowData['_super_attribute_price_corr'], -1),
                            'pricing_value' => (double)rtrim($rowData['_super_attribute_price_corr'], '%'),
                            'website_id' => 0,
                        ];
                    }
                }
            }
            // save last product super data
            $this->_processSuperData($productSuperData, $superAttributes);

            // remove old data if needed
            if ($this->_entityModel->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND &&
                $superAttributes['attributes']
            ) {
                $quoted = $connection->quoteInto('IN (?)', array_keys($superAttributes['attributes']));
                $connection->delete($mainTable, "product_id {$quoted}");
                $connection->delete($linkTable, "parent_id {$quoted}");
                $connection->delete($relationTable, "parent_id {$quoted}");
            }
            $mainData = [];

            foreach ($superAttributes['attributes'] as $productId => $attributesData) {
                foreach ($attributesData as $attrId => $row) {
                    $row['product_id'] = $productId;
                    $row['attribute_id'] = $attrId;
                    $mainData[] = $row;
                }
            }
            if ($mainData) {
                $connection->insertOnDuplicate($mainTable, $mainData);
            }
            if ($superAttributes['labels']) {
                $connection->insertOnDuplicate($labelTable, $superAttributes['labels']);
            }
            if ($superAttributes['pricing']) {
                $connection->insertOnDuplicate(
                    $priceTable,
                    $superAttributes['pricing'],
                    ['is_percent', 'pricing_value']
                );
            }
            if ($superAttributes['super_link']) {
                $connection->insertOnDuplicate($linkTable, $superAttributes['super_link']);
            }
            if ($superAttributes['relation']) {
                $connection->insertOnDuplicate($relationTable, $superAttributes['relation']);
            }
        }
        return $this;
    }
}
