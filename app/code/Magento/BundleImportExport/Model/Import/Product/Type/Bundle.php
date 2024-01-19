<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleImportExport\Model\Import\Product\Type;

use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType as CatalogImportExportAbstractType;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Import entity Bundle product type.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle extends CatalogImportExportAbstractType implements
    ResetAfterRequestInterface
{
    /**
     * Delimiter before product option value.
     */
    public const BEFORE_OPTION_VALUE_DELIMITER = ';';

    public const PAIR_VALUE_SEPARATOR = '=';

    /**
     * Dynamic value.
     */
    public const VALUE_DYNAMIC = 'dynamic';

    /**
     * Fixed value.
     */
    public const VALUE_FIXED = 'fixed';

    public const NOT_FIXED_DYNAMIC_ATTRIBUTE = 'price_view';

    public const SELECTION_PRICE_TYPE_FIXED = 0;

    public const SELECTION_PRICE_TYPE_PERCENT = 1;

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
     * Custom fields mapping for bundle product.
     *
     * @var array
     */
    protected $_customFieldsMapping = [
        'price_type' => 'bundle_price_type',
        'shipment_type' => 'bundle_shipment_type',
        'price_view' => 'bundle_price_view',
        'weight_type' => 'bundle_weight_type',
        'sku_type' => 'bundle_sku_type',
    ];

    /**
     * Bundle field mapping for bundle product with selection.
     *
     * @var array
     */
    protected $_bundleFieldMapping = [
        'is_default' => 'default',
        'selection_price_value' => 'price',
        'selection_qty' => 'default_qty',
    ];

    /**
     * Option type mapping for bundle product.
     *
     * @var array
     */
    protected $_optionTypeMapping = [
        'dropdown' => 'select',
        'radiobutton' => 'radio',
        'checkbox' => 'checkbox',
        'multiselect' => 'multi',
    ];

    /**
     * @var Bundle\RelationsDataSaver
     */
    private $relationsDataSaver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $storeCodeToId = [];

    /**
     * @param AttributeSetCollectionFactory $attrSetColFac
     * @param AttributeCollectionFactory $prodAttrColFac
     * @param ResourceConnection $resource
     * @param array $params
     * @param MetadataPool|null $metadataPool
     * @param Bundle\RelationsDataSaver|null $relationsDataSaver
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        AttributeSetCollectionFactory $attrSetColFac,
        AttributeCollectionFactory $prodAttrColFac,
        ResourceConnection $resource,
        array $params,
        MetadataPool $metadataPool = null,
        Bundle\RelationsDataSaver $relationsDataSaver = null,
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $metadataPool);

        $this->relationsDataSaver = $relationsDataSaver
            ?: ObjectManager::getInstance()->get(Bundle\RelationsDataSaver::class);
        $this->storeManager = $storeManager
            ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
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
        if (empty($rowData['bundle_values'])) {
            return [];
        }

        if (is_string($rowData['bundle_values'])) {
            $rowData['bundle_values'] = str_replace(
                self::BEFORE_OPTION_VALUE_DELIMITER,
                $this->_entityModel->getMultipleValueSeparator(),
                $rowData['bundle_values']
            );
            $selections = explode(
                Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData['bundle_values']
            );
        } else {
            $selections = $rowData['bundle_values'];
        }

        foreach ($selections as $selection) {
            $option = is_string($selection)
                ? $this->parseOption(explode($this->_entityModel->getMultipleValueSeparator(), $selection))
                : $selection;

            if (isset($option['sku'], $option['name'])) {
                $this->_cachedSkus[] = $option['sku'];
                if (!isset($this->_cachedOptions[$entityId][$option['name']])) {
                    $this->_cachedOptions[$entityId][$option['name']] = [];
                    $this->_cachedOptions[$entityId][$option['name']] = $option;
                    $this->_cachedOptions[$entityId][$option['name']]['selections'] = [];
                }
                $this->_cachedOptions[$entityId][$option['name']]['selections'][$option['sku']] = $option;
                $this->_cachedOptionSelectQuery[] = [(int)$entityId, $option['name']];
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
            $keyValue = $keyValue ? trim($keyValue) : '';
            $pos = strpos($keyValue, self::PAIR_VALUE_SEPARATOR);
            if ($pos !== false) {
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
     * @return array
     */
    protected function populateOptionValueTemplate(array $option, int $optionId, int $storeId = 0): array
    {
        $optionValues = [];
        if (isset($option['name'], $option['parent_id']) && $optionId) {
            $pattern = '/^name[_]?(.*)/';
            $keys = array_keys($option);
            $optionNames = preg_grep($pattern, $keys);
            foreach ($optionNames as $optionName) {
                preg_match($pattern, $optionName, $storeCodes);
                $storeCode = array_pop($storeCodes);
                $storeId = $storeCode ? $this->getStoreIdByCode($storeCode) : $storeId;
                $optionValues[] = [
                    'option_id' => $optionId,
                    'parent_product_id' => $option['parent_id'],
                    'store_id' => $storeId,
                    'title' => $option[$optionName],
                ];
            }
        }

        return $optionValues;
    }

    /**
     * Populate the option value template.
     *
     * @param array $selection
     * @param int $optionId
     * @param int $parentId
     * @param int $index
     * @return array|bool
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
            'selection_can_change_qty' => isset($selection['can_change_qty'])
                ? ($selection['can_change_qty'] ? 1 : 0) : 1,
        ];
        if (isset($selection['selection_id'])) {
            $populatedSelection['selection_id'] = $selection['selection_id'];
        }
        return $populatedSelection;
    }

    /**
     * Set cache option selection
     *
     * @param array $existingSelection
     * @param string $optionTitle
     * @param string $selectIndex
     * @param string $key
     * @param string $origKey
     * @return void
     */
    private function setCacheOptionSelection(
        array $existingSelection,
        string $optionTitle,
        string $selectIndex,
        string $key,
        string $origKey
    ): void {
        if (!isset($this->_cachedOptions[$existingSelection['parent_product_id']]
                [$optionTitle]['selections'][$selectIndex][$key])
        ) {
            $this->_cachedOptions[$existingSelection['parent_product_id']]
            [$optionTitle]['selections'][$selectIndex][$key] = $existingSelection[$origKey];
        }
    }

    /**
     * Deprecated method for retrieving mapping between skus and products.
     *
     * @deprecated 100.3.0 Misspelled method
     * @see retrieveProductsByCachedSkus
     */
    protected function retrieveProducsByCachedSkus()
    {
        return $this->retrieveProductsByCachedSkus();
    }

    /**
     * Retrieve mapping between skus and products.
     *
     * @return CatalogImportExportAbstractType
     */
    protected function retrieveProductsByCachedSkus()
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
     * @return CatalogImportExportAbstractType
     */
    public function saveData()
    {
        if ($this->_entityModel->getBehavior() == Import::BEHAVIOR_DELETE) {
            $productIds = [];
            $newSku = $this->_entityModel->getNewSku();
            while ($bunch = $this->_entityModel->getNextBunch()) {
                foreach ($bunch as $rowData) {
                    $productData = $newSku[strtolower($rowData[Product::COL_SKU] ?? '')];
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
                    $productData = $newSku[strtolower($rowData[Product::COL_SKU] ?? '')];
                    if ($this->_type != $productData['type_id']) {
                        continue;
                    }
                    $this->parseSelections($rowData, $productData[$this->getProductEntityLinkField()]);
                }
                if (!empty($this->_cachedOptions)) {
                    $this->retrieveProductsByCachedSkus();
                    $this->populateExistingOptions();
                    $this->insertOptions();
                    $this->insertSelections();
                    $this->insertParentChildRelations();
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
     * @return CatalogImportExportAbstractType
     */
    protected function populateExistingOptions()
    {
        $select = $this->connection->select()->from(
            ['bo' => $this->_resource->getTableName('catalog_product_bundle_option')],
            ['option_id', 'parent_id', 'required', 'position', 'type']
        )->joinLeft(
            ['bov' => $this->_resource->getTableName('catalog_product_bundle_option_value')],
            'bo.option_id = bov.option_id',
            ['value_id', 'title']
        );
        $orWhere = false;
        foreach ($this->_cachedOptionSelectQuery as $item) {
            if ($orWhere) {
                $select->orWhere('parent_id = ' . $item[0] . ' AND title = ?', $item[1]);
            } else {
                $select->where('parent_id = ' . $item[0] . ' AND title = ?', $item[1]);
                $orWhere = true;
            }
        }
        $existingOptions = $this->connection->fetchAssoc($select);
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
     * @return CatalogImportExportAbstractType
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
            if (array_key_exists($existingSelection['parent_product_id'], $this->_cachedOptions)) {
                $cachedOptionsSelections = $this->_cachedOptions[$existingSelection['parent_product_id']][$optionTitle]['selections'];
                foreach ($cachedOptionsSelections as $selectIndex => $selection) {
                    $productId = $this->_cachedSkuToProducts[$selection['sku']];
                    if ($productId == $existingSelection['product_id']) {
                        foreach (array_keys($existingSelection) as $origKey) {
                            $key = $this->_bundleFieldMapping[$origKey] ?? $origKey;
                            $this->setCacheOptionSelection($existingSelection, (string) $optionTitle,
                                (string) $selectIndex, (string) $key, (string) $origKey);
                        }
                        break;
                    }
                }
            }
        }
        // @codingStandardsIgnoreEnd
        return $this;
    }

    /**
     * Insert options.
     *
     * @return CatalogImportExportAbstractType
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
                ['bo' => $this->_resource->getTableName('catalog_product_bundle_option')],
                ['option_id', 'position', 'parent_id']
            )->joinLeft(
                ['bov' => $this->_resource->getTableName('catalog_product_bundle_option_value')],
                'bo.option_id = bov.option_id',
                ['title']
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
     *
     * @param array $optionIds
     * @return array
     */
    protected function populateInsertOptionValues(array $optionIds): array
    {
        $optionValues = [];
        foreach ($this->_cachedOptions as $entityId => $options) {
            foreach ($options as $key => $option) {
                foreach ($optionIds as $optionId => $assoc) {
                    if ($assoc['position'] == $this->_cachedOptions[$entityId][$key]['index'] &&
                        $assoc['parent_id'] == $entityId &&
                        (empty($assoc['title']) || $assoc['title'] == $this->_cachedOptions[$entityId][$key]['name'])
                    ) {
                        $option['parent_id'] = $entityId;
                        $optionValues[] = $this->populateOptionValueTemplate($option, $optionId);
                        $this->_cachedOptions[$entityId][$key]['option_id'] = $optionId;
                        break;
                    }
                }
            }
        }

        return array_merge([], ...$optionValues);
    }

    /**
     * Insert selections.
     *
     * @return CatalogImportExportAbstractType
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
     * Insert parent/child product relations
     *
     * @return CatalogImportExportAbstractType
     */
    private function insertParentChildRelations()
    {
        foreach ($this->_cachedOptions as $productId => $options) {
            $childIds = [];
            foreach ($options as $option) {
                foreach ($option['selections'] as $selection) {
                    if (isset($this->_cachedSkuToProducts[$selection['sku']])) {
                        $childIds[] = $this->_cachedSkuToProducts[$selection['sku']];
                    }
                }

                $this->relationsDataSaver->saveProductRelations($productId, $childIds);
            }
        }

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

        return $this;
    }

    /**
     * Delete options and selections.
     *
     * @param array $productIds
     *
     * @return CatalogImportExportAbstractType
     */
    protected function deleteOptionsAndSelections($productIds)
    {
        if (empty($productIds)) {
            return $this;
        }

        $optionTable = $this->_resource->getTableName('catalog_product_bundle_option');
        $optionValueTable = $this->_resource->getTableName('catalog_product_bundle_option_value');
        $selectionTable = $this->_resource->getTableName('catalog_product_bundle_selection');
        $valuesIds = $this->connection->fetchAssoc(
            $this->connection->select()->from(
                ['bov' => $optionValueTable],
                ['value_id']
            )->joinLeft(
                ['bo' => $optionTable],
                'bo.option_id = bov.option_id',
                ['option_id']
            )->where(
                'parent_id IN (?)',
                $productIds
            )
        );
        $this->connection->delete(
            $optionValueTable,
            $this->connection->quoteInto('value_id IN (?)', array_keys($valuesIds))
        );
        $this->connection->delete(
            $optionTable,
            $this->connection->quoteInto('parent_id IN (?)', $productIds)
        );
        $this->connection->delete(
            $selectionTable,
            $this->connection->quoteInto('parent_product_id IN (?)', $productIds)
        );
        return $this;
    }

    /**
     * Clear cached values between bunches
     *
     * @return CatalogImportExportAbstractType
     */
    protected function clear()
    {
        $this->_cachedOptions = [];
        $this->_cachedOptionSelectQuery = [];
        $this->_cachedSkus = [];
        $this->_cachedSkuToProducts = [];
        return $this;
    }

    /**
     * Get store id by store code.
     *
     * @param string $storeCode
     * @return int
     */
    private function getStoreIdByCode(string $storeCode): int
    {
        if (!isset($this->storeCodeToId[$storeCode])) {
            /** @var $store Store */
            foreach ($this->storeManager->getStores() as $store) {
                $this->storeCodeToId[$store->getCode()] = (int)$store->getId();
            }
        }

        return $this->storeCodeToId[$storeCode];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_cachedOptions = [];
        $this->_cachedSkus = [];
        $this->_cachedOptionSelectQuery = [];
        $this->_cachedSkuToProducts = [];
    }
}
