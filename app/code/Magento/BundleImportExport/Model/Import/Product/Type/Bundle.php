<?php

/**
 * Import entity of bundle product type
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Import\Product\Type;

use \Magento\Framework\App\ObjectManager;
use \Magento\Bundle\Model\Product\Price as BundlePrice;
use \Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\CatalogImportExport\Model\Import\Product;

/**
 * Class Bundle
 * @package Magento\BundleImportExport\Model\Import\Product\Type
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Bundle extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{

    /**
     * Delimiter before product option value.
     */
    const BEFORE_OPTION_VALUE_DELIMITER = ';';

    /**
     * Pair value separator.
     */
    const PAIR_VALUE_SEPARATOR = '=';

    /**
     * Dynamic value.
     */
    const VALUE_DYNAMIC = 'dynamic';

    /**
     * Fixed value.
     */
    const VALUE_FIXED = 'fixed';

    /**
     * Not fixed dynamic attribute.
     */
    const NOT_FIXED_DYNAMIC_ATTRIBUTE = 'price_view';

    /**
     * Selection price type fixed.
     */
    const SELECTION_PRICE_TYPE_FIXED = 0;

    /**
     * Selection price type percent.
     */
    const SELECTION_PRICE_TYPE_PERCENT = 1;

    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $_cachedOptions = [];

    /**
     * Array of cached skus.
     *
     * @var array
     */
    protected $_cachedSkus = [];

    /**
     * Mapping array between cached skus and products.
     *
     * @var array
     */
    protected $_cachedSkuToProducts = [];

    /**
     * Array of queries selecting cached options.
     *
     * @var array
     */
    protected $_cachedOptionSelectQuery = [];

    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_specialAttributes = [
        'price_type',
        'weight_type',
        'sku_type',
    ];

    /**
     * Custom fields mapping.
     *
     * @inherited
     */
    protected $_customFieldsMapping = [
        'price_type' => 'bundle_price_type',
        'shipment_type' => 'bundle_shipment_type',
        'price_view' => 'bundle_price_view',
        'weight_type' => 'bundle_weight_type',
        'sku_type' => 'bundle_sku_type',
    ];

    /**
     * Bundle field mapping.
     *
     * @var array
     */
    protected $_bundleFieldMapping = [
        'is_default' => 'default',
        'selection_price_value' => 'price',
        'selection_qty' => 'default_qty',
    ];

    /**
     * Option type mapping.
     *
     * @var array
     */
    protected $_optionTypeMapping = [
        'dropdown' => 'select',
        'radiobutton' => 'radio',
        'checkbox'  => 'checkbox',
        'multiselect' => 'multi',
    ];

    /**
     * @var Bundle\RelationsDataSaver
     * @since 2.2.0
     */
    private $relationsDataSaver;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $params
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param Bundle\RelationsDataSaver|null $relationsDataSaver
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        Bundle\RelationsDataSaver $relationsDataSaver = null
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $metadataPool);

        $this->relationsDataSaver = $relationsDataSaver
            ?: ObjectManager::getInstance()->get(Bundle\RelationsDataSaver::class);
    }

    /**
     * Parse selections.
     *
     * @param array $rowData
     * @param int $entityId
     *
     * @return array
     */
    protected function parseSelections($rowData, $entityId)
    {
        $rowData['bundle_values'] = str_replace(
            self::BEFORE_OPTION_VALUE_DELIMITER,
            $this->_entityModel->getMultipleValueSeparator(),
            $rowData['bundle_values']
        );
        $selections = explode(
            Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowData['bundle_values']
        );
        foreach ($selections as $selection) {
            $values = explode($this->_entityModel->getMultipleValueSeparator(), $selection);
            $option = $this->parseOption($values);
            if (isset($option['sku']) && isset($option['name'])) {
                if (!isset($this->_cachedOptions[$entityId])) {
                    $this->_cachedOptions[$entityId] = [];
                }
                $this->_cachedSkus[] = $option['sku'];
                if (!isset($this->_cachedOptions[$entityId][$option['name']])) {
                    $this->_cachedOptions[$entityId][$option['name']] = [];
                    $this->_cachedOptions[$entityId][$option['name']] = $option;
                    $this->_cachedOptions[$entityId][$option['name']]['selections'] = [];
                }
                $this->_cachedOptions[$entityId][$option['name']]['selections'][] = $option;
                $this->_cachedOptionSelectQuery[] =
                    $this->connection->quoteInto(
                        '(parent_id = ' . (int)$entityId . ' AND title = ?)',
                        $option['name']
                    );
            }
        }
        return $selections;
    }

    /**
     * Parse the option.
     *
     * @param array $values
     *
     * @return array
     */
    protected function parseOption($values)
    {
        $option = [];
        foreach ($values as $keyValue) {
            $keyValue = trim($keyValue);
            if ($pos = strpos($keyValue, self::PAIR_VALUE_SEPARATOR)) {
                $key = substr($keyValue, 0, $pos);
                $value = substr($keyValue, $pos + 1);
                if ($key == 'type') {
                    if (isset($this->_optionTypeMapping[$value])) {
                        $value = $this->_optionTypeMapping[$value];
                    }
                }
                $option[$key] = $value;
            }
        }
        return $option;
    }

    /**
     * Populate the option template.
     *
     * @param array $option
     * @param int $entityId
     * @param int $index
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populateOptionTemplate($option, $entityId, $index = null)
    {
        $populatedOption = [
            'option_id' => null,
            'parent_id' => $entityId,
            'required' => isset($option['required']) ? $option['required'] : 1,
            'position' => ($index === null ? 0 : $index),
            'type' => isset($option['type']) ? $option['type'] : 'select',
        ];
        if (isset($option['option_id'])) {
            $populatedOption['option_id'] = $option['option_id'];
        }
        return $populatedOption;
    }

    /**
     * Populate the option value template.
     *
     * @param array $option
     * @param int $optionId
     * @param int $storeId
     *
     * @return array|bool
     */
    protected function populateOptionValueTemplate($option, $optionId, $storeId = 0)
    {
        if (!isset($option['name']) || !isset($option['parent_id']) || !$optionId) {
            return false;
        }
        return [
            'option_id' => $optionId,
            'parent_product_id' => $option['parent_id'],
            'store_id' => $storeId,
            'title' => $option['name'],
        ];
    }

    /**
     * Populate the option value template.
     *
     * @param array $selection
     * @param int $optionId
     * @param int $parentId
     * @param int $index
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function populateSelectionTemplate($selection, $optionId, $parentId, $index)
    {
        if (!isset($selection['parent_product_id'])) {
            if (!isset($this->_cachedSkuToProducts[$selection['sku']])) {
                return false;
            }
            $productId = $this->_cachedSkuToProducts[$selection['sku']];
        } else {
            $productId = $selection['product_id'];
        }
        $populatedSelection = [
            'selection_id' => null,
            'option_id' => (int)$optionId,
            'parent_product_id' => (int)$parentId,
            'product_id' => (int)$productId,
            'position' => (int)$index,
            'is_default' => (isset($selection['default']) && $selection['default']) ? 1 : 0,
            'selection_price_type' => (isset($selection['price_type']) && $selection['price_type'] == self::VALUE_FIXED)
                ? self::SELECTION_PRICE_TYPE_FIXED : self::SELECTION_PRICE_TYPE_PERCENT,
            'selection_price_value' => (isset($selection['price'])) ? (float)$selection['price'] : 0.0,
            'selection_qty' => (isset($selection['default_qty'])) ? (float)$selection['default_qty'] : 1.0,
            'selection_can_change_qty' => 1,
        ];
        if (isset($selection['selection_id'])) {
            $populatedSelection['selection_id'] = $selection['selection_id'];
        }
        return $populatedSelection;
    }

    /**
     * Retrieve mapping between skus and products.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function retrieveProducsByCachedSkus()
    {
        $this->_cachedSkuToProducts = $this->connection->fetchPairs(
            $this->connection->select()->from(
                $this->_resource->getTableName('catalog_product_entity'),
                ['sku', 'entity_id']
            )->where(
                'sku IN (?)',
                $this->_cachedSkus
            )
        );
        return $this;
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveData()
    {
        if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            $productIds = [];
            $newSku = $this->_entityModel->getNewSku();
            while ($bunch = $this->_entityModel->getNextBunch()) {
                foreach ($bunch as $rowNum => $rowData) {
                    $productData = $newSku[strtolower($rowData[Product::COL_SKU])];
                    $productIds[] = $productData[$this->getProductEntityLinkField()];
                }
                $this->deleteOptionsAndSelections($productIds);
            }
        } else {
            $newSku = $this->_entityModel->getNewSku();
            while ($bunch = $this->_entityModel->getNextBunch()) {
                foreach ($bunch as $rowNum => $rowData) {
                    if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                        continue;
                    }
                    $productData = $newSku[strtolower($rowData[Product::COL_SKU])];
                    if ($this->_type != $productData['type_id']) {
                        continue;
                    }
                    $this->parseSelections($rowData, $productData[$this->getProductEntityLinkField()]);
                }
                if (!empty($this->_cachedOptions)) {
                    $this->retrieveProducsByCachedSkus();
                    $this->populateExistingOptions();
                    $this->insertOptions();
                    $this->insertSelections();
                    $this->clear();
                }
            }
        }
        return $this;
    }

    /**
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct
     * @return bool
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        if (isset($rowData['bundle_price_type']) && $rowData['bundle_price_type'] == 'dynamic') {
            $rowData['price'] = isset($rowData['price']) && $rowData['price'] ? $rowData['price'] : '0.00';
        }

        return parent::isRowValid($rowData, $rowNum, $isNewProduct);
    }

    /**
     * Prepare attributes with default value for save.
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     * @return array
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = parent::prepareAttributesWithDefaultValueForSave($rowData, $withDefaultValue);
        $resultAttrs = array_merge($resultAttrs, $this->transformBundleCustomAttributes($rowData));
        return $resultAttrs;
    }

    /**
     * Transform dynamic/fixed values to integer.
     *
     * @param array $rowData
     * @return array
     */
    protected function transformBundleCustomAttributes($rowData)
    {
        $resultAttrs = [];
        foreach ($this->_customFieldsMapping as $oldKey => $newKey) {
            if (isset($rowData[$oldKey])) {
                switch ($newKey) {
                    case $this->_customFieldsMapping['price_view']:
                        break;
                    case $this->_customFieldsMapping['shipment_type']:
                        $resultAttrs[$oldKey] = (($rowData[$oldKey] == 'separately') ?
                            AbstractType::SHIPMENT_SEPARATELY :
                            AbstractType::SHIPMENT_TOGETHER);
                        break;
                    default:
                        $resultAttrs[$oldKey] = (($rowData[$oldKey] == self::VALUE_FIXED) ?
                            BundlePrice::PRICE_TYPE_FIXED :
                            BundlePrice::PRICE_TYPE_DYNAMIC);
                }
            }
        }

        return $resultAttrs;
    }

    /**
     * Populates existing options.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function populateExistingOptions()
    {
        $existingOptions = $this->connection->fetchAssoc(
            $this->connection->select()->from(
                ['bo' => $this->_resource->getTableName('catalog_product_bundle_option')],
                ['option_id', 'parent_id', 'required', 'position', 'type']
            )->joinLeft(
                ['bov' => $this->_resource->getTableName('catalog_product_bundle_option_value')],
                'bo.option_id = bov.option_id',
                ['value_id', 'title']
            )->where(
                implode(' OR ', $this->_cachedOptionSelectQuery)
            )
        );
        foreach ($existingOptions as $optionId => $option) {
            $this->_cachedOptions[$option['parent_id']][$option['title']]['option_id'] = $optionId;
            foreach ($option as $key => $value) {
                if (!isset($this->_cachedOptions[$option['parent_id']][$option['title']][$key])) {
                    $this->_cachedOptions[$option['parent_id']][$option['title']][$key] = $value;
                }
            }
        }
        $this->populateExistingSelections($existingOptions);
        return $this;
    }

    /**
     * Populate existing selections.
     *
     * @param array $existingOptions
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function populateExistingSelections($existingOptions)
    {
        //@codingStandardsIgnoreStart
        $existingSelections = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('catalog_product_bundle_selection')
            )->where(
                'option_id IN (?)',
                array_keys($existingOptions)
            )
        );
        foreach ($existingSelections as $existingSelection) {
            $optionTitle = $existingOptions[$existingSelection['option_id']]['title'];
            $cachedOptionsSelections = $this->_cachedOptions[$existingSelection['parent_product_id']][$optionTitle]['selections'];
            foreach ($cachedOptionsSelections as $selectIndex => $selection) {
                $productId = $this->_cachedSkuToProducts[$selection['sku']];
                if ($productId == $existingSelection['product_id']) {
                    foreach (array_keys($existingSelection) as $origKey) {
                        $key = isset($this->_bundleFieldMapping[$origKey])
                            ? $this->_bundleFieldMapping[$origKey]
                            : $origKey;
                        if (
                            !isset($this->_cachedOptions[$existingSelection['parent_product_id']][$optionTitle]['selections'][$selectIndex][$key])
                        ) {
                            $this->_cachedOptions[$existingSelection['parent_product_id']][$optionTitle]['selections'][$selectIndex][$key] =
                                $existingSelection[$origKey];
                        }
                    }
                    break;
                }
            }
        }
        // @codingStandardsIgnoreEnd
        return $this;
    }

    /**
     * Insert options.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function insertOptions()
    {
        $productIds = [];
        $insert = [];

        foreach ($this->_cachedOptions as $entityId => $options) {
            $index = 0;
            $productIds[] = $entityId;
            foreach ($options as $key => $option) {
                if (isset($option['position'])) {
                    $index = $option['position'];
                }
                if ($tmpArray = $this->populateOptionTemplate($option, $entityId, $index)) {
                    $insert[] = $tmpArray;
                    $this->_cachedOptions[$entityId][$key]['index'] = $index;
                    $index++;
                }
            }
        }

        $this->relationsDataSaver->saveOptions($insert);

        $optionIds = $this->connection->fetchAssoc(
            $this->connection->select()->from(
                $this->_resource->getTableName('catalog_product_bundle_option'),
                ['option_id', 'position', 'parent_id']
            )->where(
                'parent_id IN (?)',
                $productIds
            )
        );

        $this->relationsDataSaver->saveOptionValues(
            $this->populateInsertOptionValues($optionIds)
        );

        return $this;
    }

    /**
     * Populate array for insert option values
     * @param array $optionIds
     * @return array
     */
    protected function populateInsertOptionValues($optionIds)
    {
        $insertValues = [];
        foreach ($this->_cachedOptions as $entityId => $options) {
            foreach ($options as $key => $option) {
                foreach ($optionIds as $optionId => $assoc) {
                    if ($assoc['position'] == $this->_cachedOptions[$entityId][$key]['index']
                        && $assoc['parent_id'] == $entityId) {
                        $option['parent_id'] = $entityId;
                        $insertValues[] = $this->populateOptionValueTemplate($option, $optionId);
                        $this->_cachedOptions[$entityId][$key]['option_id'] = $optionId;
                        break;
                    }
                }
            }
        }
        return $insertValues;
    }

    /**
     * Insert selections.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function insertSelections()
    {
        $selections = [];

        foreach ($this->_cachedOptions as $productId => $options) {
            foreach ($options as $option) {
                $index = 0;
                foreach ($option['selections'] as $selection) {
                    if (isset($selection['position'])) {
                        $index = $selection['position'];
                    }
                    if ($tmpArray = $this->populateSelectionTemplate(
                        $selection,
                        $option['option_id'],
                        $productId,
                        $index
                    )) {
                        $selections[] = $tmpArray;
                        $index++;
                    }
                }
            }
        }

        $this->relationsDataSaver->saveSelections($selections);

        return $this;
    }

    /**
     * Initialize attributes parameters for all attributes' sets.
     *
     * @return $this
     */
    protected function _initAttributes()
    {
        parent::_initAttributes();

        $options = [
            self::VALUE_DYNAMIC => BundlePrice::PRICE_TYPE_DYNAMIC,
            self::VALUE_FIXED => BundlePrice::PRICE_TYPE_FIXED,
        ];

        foreach ($this->_specialAttributes as $attributeCode) {
            if (isset(self::$attributeCodeToId[$attributeCode]) && $id = self::$attributeCodeToId[$attributeCode]) {
                self::$commonAttributesCache[$id]['type'] = 'select';
                self::$commonAttributesCache[$id]['options'] = $options;

                foreach ($this->_attributes as $attrSetName => $attrSetValue) {
                    if (isset($attrSetValue[$attributeCode])) {
                        $this->_attributes[$attrSetName][$attributeCode]['type'] = 'select';
                        $this->_attributes[$attrSetName][$attributeCode]['options'] = $options;
                    }
                }
            }
        }
    }

    /**
     * Delete options and selections.
     *
     * @param array $productIds
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deleteOptionsAndSelections($productIds)
    {
        $optionTable = $this->_resource->getTableName('catalog_product_bundle_option');
        $optionValueTable = $this->_resource->getTableName('catalog_product_bundle_option_value');
        $valuesIds =  $this->connection->fetchAssoc($this->connection->select()->from(
            ['bov' => $optionValueTable],
            ['value_id']
        )->joinLeft(
            ['bo' => $optionTable],
            'bo.option_id = bov.option_id',
            ['option_id']
        )->where(
            'parent_id IN (?)',
            $productIds
        ));
        $this->connection->delete(
            $optionTable,
            $this->connection->quoteInto('value_id IN (?)', array_keys($valuesIds))
        );
        $productIdsInWhere = $this->connection->quoteInto('parent_id IN (?)', $productIds);
        $this->connection->delete(
            $optionTable,
            $this->connection->quoteInto('parent_id IN (?)', $productIdsInWhere)
        );
        $this->connection->delete(
            $optionTable,
            $this->connection->quoteInto('parent_product_id IN (?)', $productIdsInWhere)
        );
        return $this;
    }

    /**
     * Clear cached values between bunches
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function clear()
    {
        $this->_cachedOptions = [];
        $this->_cachedOptionSelectQuery = [];
        $this->_cachedSkus = [];
        $this->_cachedSkuToProducts = [];
        return $this;
    }
}
