<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

/**
 * Export entity product model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\ImportExport\Model\Export\Entity\AbstractEntity
{
    /**
     * Attributes that should be exported
     *
     * @var string[]
     */
    protected $_bannedAttributes = ['media_gallery'];

    /**
     * Value that means all entities (e.g. websites, groups etc.)
     */
    const VALUE_ALL = 'all';

    /**
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COL_STORE = '_store';

    const COL_ATTR_SET = '_attribute_set';

    const COL_TYPE = '_type';

    const COL_CATEGORY = '_category';

    const COL_ROOT_CATEGORY = '_root_category';

    const COL_SKU = 'sku';

    const COL_VISIBILITY = 'visibility';

    /**
     * Pairs of attribute set ID-to-name.
     *
     * @var array
     */
    protected $_attrSetIdToName = [];

    /**
     * Categories ID to text-path hash.
     *
     * @var array
     */
    protected $_categories = [];

    /**
     * Root category names for each category
     *
     * @var array
     */
    protected $_rootCategories = [];

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = [
        'status',
        'tax_class_id',
        'visibility',
        'gift_message_available',
        'custom_design',
    ];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * Array of supported product types as keys with appropriate model object as value.
     *
     * @var array
     */
    protected $_productTypeModels = [];

    /**
     * Array of pairs store ID to its code.
     *
     * @var array
     */
    protected $_storeIdToCode = [];

    /**
     * Website ID-to-code.
     *
     * @var array
     */
    protected $_websiteIdToCode = [];

    /**
     * Attribute types
     *
     * @var array
     */
    protected $_attributeTypes = [];

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_entityCollection;

    /**
     * Items per page for collection limitation
     *
     * @var null
     */
    protected $_itemsPerPage = null;

    /**
     * Header columns for export file
     *
     * @var array
     */
    protected $_headerColumns = [];

    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface
     */
    protected $_exportConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Model\Resource\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection
     */
    protected $_attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $_categoryColFactory;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resourceModel;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Option\Collection
     */
    protected $_optionColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected $_attributeColFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product\Type\Factory
     */
    protected $_typeFactory;

    /**
     * Provider of product link types
     *
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider
     */
    protected $_linkTypeProvider;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface
     */
    protected $rowCustomizer;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFactory
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory
     * @param \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\Resource\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
     * @param Product\Type\Factory $_typeFactory
     * @param \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Resource\Product\Collection $collection,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Catalog\Model\Resource\ProductFactory $productFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory,
        \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory $itemFactory,
        \Magento\Catalog\Model\Resource\Product\Option\CollectionFactory $optionColFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer
    ) {
        $this->_entityCollection = $collection;
        $this->_exportConfig = $exportConfig;
        $this->_logger = $logger;
        $this->_productFactory = $productFactory;
        $this->_attrSetColFactory = $attrSetColFactory;
        $this->_categoryColFactory = $categoryColFactory;
        $this->_resourceModel = $resource;
        $this->_itemFactory = $itemFactory;
        $this->_optionColFactory = $optionColFactory;
        $this->_attributeColFactory = $attributeColFactory;
        $this->_typeFactory = $_typeFactory;
        $this->_linkTypeProvider = $linkTypeProvider;
        $this->rowCustomizer = $rowCustomizer;

        parent::__construct($localeDate, $config, $resource, $storeManager);

        $this->_initTypeModels()
            ->_initAttributes()
            ->_initStores()
            ->_initAttributeSets()
            ->_initWebsites()
            ->_initCategories();
    }

    /**
     * Initialize attribute sets code-to-id pairs.
     *
     * @return $this
     */
    protected function _initAttributeSets()
    {
        $productTypeId = $this->_productFactory->create()->getTypeId();
        foreach ($this->_attrSetColFactory->create()->setEntityTypeFilter($productTypeId) as $attributeSet) {
            $this->_attrSetIdToName[$attributeSet->getId()] = $attributeSet->getAttributeSetName();
        }
        return $this;
    }

    /**
     * Initialize categories ID to text-path hash.
     *
     * @return $this
     */
    protected function _initCategories()
    {
        $collection = $this->_categoryColFactory->create()->addNameToResult();
        /* @var $collection \Magento\Catalog\Model\Resource\Category\Collection */
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize = count($structure);
            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $collection->getItemById($structure[$i])->getName();
                }
                $this->_rootCategories[$category->getId()] = array_shift($path);
                if ($pathSize > 2) {
                    $this->_categories[$category->getId()] = implode('/', $path);
                }
            }
        }
        return $this;
    }

    /**
     * Initialize product type models.
     *
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    protected function _initTypeModels()
    {
        $productTypes = $this->_exportConfig->getEntityTypes($this->getEntityTypeCode());
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            if (!($model = $this->_typeFactory->create($productTypeConfig['model']))) {
                throw new \Magento\Framework\Model\Exception(
                    "Entity type model '{$productTypeConfig['model']}' is not found"
                );
            }
            if (!$model instanceof \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType) {
                throw new \Magento\Framework\Model\Exception(
                    __(
                        'Entity type model must be an instance of'
                        . ' \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType'
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
                $this->_disabledAttrs = array_merge($this->_disabledAttrs, $model->getDisabledAttrs());
                $this->_indexValueAttributes = array_merge(
                    $this->_indexValueAttributes,
                    $model->getIndexValueAttributes()
                );
            }
        }
        if (!$this->_productTypeModels) {
            throw new \Magento\Framework\Model\Exception(__('There are no product types available for export'));
        }
        $this->_disabledAttrs = array_unique($this->_disabledAttrs);

        return $this;
    }

    /**
     * Initialize website values.
     *
     * @return $this
     */
    protected function _initWebsites()
    {
        /** @var $website \Magento\Store\Model\Website */
        foreach ($this->_storeManager->getWebsites() as $website) {
            $this->_websiteIdToCode[$website->getId()] = $website->getCode();
        }
        return $this;
    }

    /**
     * Prepare products tier prices
     *
     * @param  int[] $productIds
     * @return array
     */
    protected function _prepareTierPrices(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $select = $this->_connection->select()->from(
            $this->_resourceModel->getTableName('catalog_product_entity_tier_price')
        )->where(
            'entity_id IN(?)',
            $productIds
        );

        $rowTierPrices = [];
        $stmt = $this->_connection->query($select);
        while ($tierRow = $stmt->fetch()) {
            $rowTierPrices[$tierRow['entity_id']][] = [
                '_tier_price_customer_group' => $tierRow['all_groups']
                    ? self::VALUE_ALL
                    : $tierRow['customer_group_id'],
                '_tier_price_website' => 0 ==
                $tierRow['website_id'] ? self::VALUE_ALL : $this->_websiteIdToCode[$tierRow['website_id']],
                '_tier_price_qty' => $tierRow['qty'],
                '_tier_price_price' => $tierRow['value'],
            ];
        }

        return $rowTierPrices;
    }

    /**
     * Prepare products group prices
     *
     * @param  int[] $productIds
     * @return array
     */
    protected function _prepareGroupPrices(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $select = $this->_connection->select()->from(
            $this->_resourceModel->getTableName('catalog_product_entity_group_price')
        )->where(
            'entity_id IN(?)',
            $productIds
        );

        $rowGroupPrices = [];
        $statement = $this->_connection->query($select);
        while ($groupRow = $statement->fetch()) {
            $rowGroupPrices[$groupRow['entity_id']][] = [
                '_group_price_customer_group' => $groupRow['all_groups']
                    ? self::VALUE_ALL
                    : $groupRow['customer_group_id'],
                '_group_price_website' => 0 ==
                $groupRow['website_id'] ? self::VALUE_ALL : $this->_websiteIdToCode[$groupRow['website_id']],
                '_group_price_price' => $groupRow['value'],
            ];
        }

        return $rowGroupPrices;
    }

    /**
     * Prepare products media gallery
     *
     * @param  int[] $productIds
     * @return array
     */
    protected function _prepareMediaGallery(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $select = $this->_connection->select()->from(
            ['mg' => $this->_resourceModel->getTableName('catalog_product_entity_media_gallery')],
            [
                'mg.entity_id',
                'mg.attribute_id',
                'filename' => 'mg.value',
                'mgv.label',
                'mgv.position',
                'mgv.disabled'
            ]
        )->joinLeft(
            ['mgv' => $this->_resourceModel->getTableName('catalog_product_entity_media_gallery_value')],
            '(mg.value_id = mgv.value_id AND mgv.store_id = 0)',
            []
        )->where(
            'mg.entity_id IN(?)',
            $productIds
        );

        $rowMediaGallery = [];
        $stmt = $this->_connection->query($select);
        while ($mediaRow = $stmt->fetch()) {
            $rowMediaGallery[$mediaRow['entity_id']][] = [
                '_media_attribute_id' => $mediaRow['attribute_id'],
                '_media_image' => $mediaRow['filename'],
                '_media_label' => $mediaRow['label'],
                '_media_position' => $mediaRow['position'],
                '_media_is_disabled' => $mediaRow['disabled'],
            ];
        }

        return $rowMediaGallery;
    }

    /**
     * Prepare catalog inventory
     *
     * @param  int[] $productIds
     * @return array
     */
    protected function _prepareCatalogInventory(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $select = $this->_connection->select()->from(
            $this->_itemFactory->create()->getMainTable()
        )->where(
            'product_id IN (?)',
            $productIds
        );

        $stmt = $this->_connection->query($select);
        $stockItemRows = [];
        while ($stockItemRow = $stmt->fetch()) {
            $productId = $stockItemRow['product_id'];
            unset(
                $stockItemRow['item_id'],
                $stockItemRow['product_id'],
                $stockItemRow['low_stock_date'],
                $stockItemRow['stock_id'],
                $stockItemRow['stock_status_changed_auto']
            );
            $stockItemRows[$productId] = $stockItemRow;
        }
        return $stockItemRows;
    }

    /**
     * Prepare product links
     *
     * @param  int[] $productIds
     * @return array
     */
    protected function _prepareLinks(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $adapter = $this->_connection;
        $select = $adapter->select()->from(
            ['cpl' => $this->_resourceModel->getTableName('catalog_product_link')],
            [
                'cpl.product_id',
                'cpe.sku',
                'cpl.link_type_id',
                'position' => 'cplai.value',
                'default_qty' => 'cplad.value'
            ]
        )->joinLeft(
            ['cpe' => $this->_resourceModel->getTableName('catalog_product_entity')],
            '(cpe.entity_id = cpl.linked_product_id)',
            []
        )->joinLeft(
            ['cpla' => $this->_resourceModel->getTableName('catalog_product_link_attribute')],
            $adapter->quoteInto(
                '(cpla.link_type_id = cpl.link_type_id AND cpla.product_link_attribute_code = ?)',
                'position'
            ),
            []
        )->joinLeft(
            ['cplaq' => $this->_resourceModel->getTableName('catalog_product_link_attribute')],
            $adapter->quoteInto(
                '(cplaq.link_type_id = cpl.link_type_id AND cplaq.product_link_attribute_code = ?)',
                'qty'
            ),
            []
        )->joinLeft(
            ['cplai' => $this->_resourceModel->getTableName('catalog_product_link_attribute_int')],
            '(cplai.link_id = cpl.link_id AND cplai.product_link_attribute_id = cpla.product_link_attribute_id)',
            []
        )->joinLeft(
            ['cplad' => $this->_resourceModel->getTableName('catalog_product_link_attribute_decimal')],
            '(cplad.link_id = cpl.link_id AND cplad.product_link_attribute_id = cplaq.product_link_attribute_id)',
            []
        )->where(
            'cpl.link_type_id IN (?)',
            array_values($this->_linkTypeProvider->getLinkTypes())
        )->where(
            'cpl.product_id IN (?)',
            $productIds
        );

        $stmt = $adapter->query($select);
        $linksRows = [];
        while ($linksRow = $stmt->fetch()) {
            $linksRows[$linksRow['product_id']][$linksRow['link_type_id']][] = [
                'sku' => $linksRow['sku'],
                'position' => $linksRow['position'],
                'default_qty' => $linksRow['default_qty'],
            ];
        }

        return $linksRows;
    }

    /**
     * Update data row with information about categories. Return true, if data row was updated
     *
     * @param array &$dataRow
     * @param array &$rowCategories
     * @param int $productId
     * @return bool
     */
    protected function _updateDataWithCategoryColumns(&$dataRow, &$rowCategories, $productId)
    {
        if (!isset($rowCategories[$productId])) {
            return false;
        }

        $categoryId = array_shift($rowCategories[$productId]);
        if ($categoryId) {
            $dataRow[self::COL_ROOT_CATEGORY] = $this->_rootCategories[$categoryId];
            if (isset($this->_categories[$categoryId])) {
                $dataRow[self::COL_CATEGORY] = $this->_categories[$categoryId];
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function _getHeaderColumns()
    {
        return $this->_headerColumns;
    }

    /**
     * Set headers columns
     *
     * @param array $customOptionsData
     * @param array $stockItemRows
     * @return void
     */
    protected function _setHeaderColumns($customOptionsData, $stockItemRows)
    {
        if (!$this->_headerColumns) {
            $customOptCols = [
                '_custom_option_store',
                '_custom_option_type',
                '_custom_option_title',
                '_custom_option_is_required',
                '_custom_option_price',
                '_custom_option_sku',
                '_custom_option_max_characters',
                '_custom_option_sort_order',
                '_custom_option_row_title',
                '_custom_option_row_price',
                '_custom_option_row_sku',
                '_custom_option_row_sort',
            ];
            $this->_headerColumns = array_merge(
                [
                    self::COL_SKU,
                    self::COL_STORE,
                    self::COL_ATTR_SET,
                    self::COL_TYPE,
                    self::COL_CATEGORY,
                    self::COL_ROOT_CATEGORY,
                    '_product_websites',
                ],
                $this->_getExportAttrCodes(),
                reset($stockItemRows) ? array_keys(end($stockItemRows)) : [],
                [],
                [
                    '_related_sku',
                    '_related_position',
                    '_crosssell_sku',
                    '_crosssell_position',
                    '_upsell_sku',
                    '_upsell_position'
                ],
                ['_tier_price_website', '_tier_price_customer_group', '_tier_price_qty', '_tier_price_price'],
                ['_group_price_website', '_group_price_customer_group', '_group_price_price'],
                ['_media_attribute_id', '_media_image', '_media_label', '_media_position', '_media_is_disabled']
            );
            // have we merge custom options columns
            if ($customOptionsData) {
                $this->_headerColumns = array_merge($this->_headerColumns, $customOptCols);
            }
        }
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected function _getEntityCollection()
    {
        return $this->_entityCollection;
    }

    /**
     * Get items per page
     *
     * @return int
     */
    protected function _getItemsPerPage()
    {
        if (is_null($this->_itemsPerPage)) {
            $memoryLimit = trim(ini_get('memory_limit'));
            $lastMemoryLimitLetter = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
            switch ($lastMemoryLimitLetter) {
                case 'g':
                    $memoryLimit *= 1024;
                    // fall-through intentional
                case 'm':
                    $memoryLimit *= 1024;
                    // fall-through intentional
                case 'k':
                    $memoryLimit *= 1024;
                    break;
                default:
                    // minimum memory required by Magento
                    $memoryLimit = 250000000;
            }

            // Tested one product to have up to such size
            $memoryPerProduct = 100000;
            // Decrease memory limit to have supply
            $memoryUsagePercent = 0.8;
            // Minimum Products limit
            $minProductsLimit = 500;

            $this->_itemsPerPage = intval(
                ($memoryLimit * $memoryUsagePercent - memory_get_usage(true)) / $memoryPerProduct
            );
            if ($this->_itemsPerPage < $minProductsLimit) {
                $this->_itemsPerPage = $minProductsLimit;
            }
        }
        return $this->_itemsPerPage;
    }

    /**
     * Set page and page size to collection
     *
     * @param int $page
     * @param int $pageSize
     * @return void
     */
    protected function _paginateCollection($page, $pageSize)
    {
        $this->_getEntityCollection()->setPage($page, $pageSize);
    }

    /**
     * Export process
     *
     * @see https://jira.corp.x.com/browse/MAGETWO-7894
     * @return string
     */
    public function export()
    {
        //Execution time may be very long
        set_time_limit(0);

        $this->_prepareEntityCollection($this->_getEntityCollection());
        $this->_getEntityCollection()->setOrder('has_options', 'asc');
        $this->_getEntityCollection()->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $writer = $this->getWriter();
        $page = 0;
        while (true) {
            ++$page;
            $this->_paginateCollection($page, $this->_getItemsPerPage());
            if ($this->_getEntityCollection()->count() == 0) {
                break;
            }
            $exportData = $this->_getExportData();
            if ($page == 1) {
                $writer->setHeaderCols($this->_getHeaderColumns());
            }
            foreach ($exportData as $dataRow) {
                $writer->writeRow($dataRow);
            }
            if ($this->_getEntityCollection()->getCurPage() >= $this->_getEntityCollection()->getLastPageNumber()) {
                break;
            }
        }
        return $writer->getContents();
    }

    /**
     * Get export data for collection
     *
     * @return array
     */
    protected function _getExportData()
    {
        $exportData = [];
        try {
            $collection = $this->_getEntityCollection();
            $validAttrCodes = $this->_getExportAttrCodes();
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $dataRows = [];
            $rowCategories = [];
            $rowWebsites = [];
            $rowTierPrices = [];
            $rowGroupPrices = [];
            $rowMultiselects = [];
            $mediaGalery = [];

            // prepare multi-store values and system columns values
            foreach ($this->_storeIdToCode as $storeId => &$storeCode) {
                // go through all stores
                $collection->setStoreId($storeId);

                if ($defaultStoreId == $storeId) {
                    $collection->addCategoryIds()->addWebsiteNamesToResult();

                    // tier and group price data getting only once
                    $rowTierPrices = $this->_prepareTierPrices($collection->getAllIds());
                    $rowGroupPrices = $this->_prepareGroupPrices($collection->getAllIds());

                    // getting media gallery data
                    $mediaGalery = $this->_prepareMediaGallery($collection->getAllIds());
                }
                foreach ($collection as $itemId => $item) {
                    // go through all products
                    $rowIsEmpty = true;
                    // row is empty by default

                    foreach ($validAttrCodes as &$attrCode) {
                        // go through all valid attribute codes
                        $attrValue = $item->getData($attrCode);

                        if (!empty($this->_attributeValues[$attrCode])) {
                            if ($this->_attributeTypes[$attrCode] == 'multiselect') {
                                $attrValue = explode(',', $attrValue);
                                $attrValue = array_intersect_key(
                                    $this->_attributeValues[$attrCode],
                                    array_flip($attrValue)
                                );
                                $rowMultiselects[$storeId][$itemId][$attrCode] = $attrValue;
                            } else {
                                if (isset($this->_attributeValues[$attrCode][$attrValue])) {
                                    $attrValue = $this->_attributeValues[$attrCode][$attrValue];
                                } else {
                                    $attrValue = null;
                                }
                            }
                        }
                        // do not save value same as default or not existent
                        if ($storeId != $defaultStoreId && isset(
                            $dataRows[$itemId][$defaultStoreId][$attrCode]
                        ) && $dataRows[$itemId][$defaultStoreId][$attrCode] == $attrValue
                        ) {
                            $attrValue = null;
                        }
                        if (is_scalar($attrValue)) {
                            $dataRows[$itemId][$storeId][$attrCode] = $attrValue;
                            // mark row as not empty
                            $rowIsEmpty = false;
                        }
                        if (!empty($rowMultiselects[$storeId][$itemId][$attrCode])) {
                            $rowIsEmpty = false;
                        }
                    }
                    if ($rowIsEmpty) {
                        // remove empty rows
                        unset($dataRows[$itemId][$storeId]);
                    } else {
                        $attrSetId = $item->getAttributeSetId();
                        $dataRows[$itemId][$storeId][self::COL_STORE] = $storeCode;
                        $dataRows[$itemId][$storeId][self::COL_ATTR_SET] = $this->_attrSetIdToName[$attrSetId];
                        $dataRows[$itemId][$storeId][self::COL_TYPE] = $item->getTypeId();

                        if ($defaultStoreId == $storeId) {
                            $rowWebsites[$itemId] = array_intersect(
                                array_keys($this->_websiteIdToCode),
                                $item->getWebsites()
                            );
                            $rowCategories[$itemId] = $item->getCategoryIds();
                        }
                    }
                    $item = null;
                }
                $collection->clear();
            }

            // remove unused categories
            $allCategoriesIds = array_merge(array_keys($this->_categories), array_keys($this->_rootCategories));
            foreach ($rowCategories as &$categories) {
                $categories = array_intersect($categories, $allCategoriesIds);
            }

            // prepare catalog inventory information
            $productIds = array_keys($dataRows);
            $stockItemRows = $this->_prepareCatalogInventory($productIds);

            // prepare links information
            $linksRows = $this->_prepareLinks($productIds);
            $linkIdColPrefix = [];
            foreach ($this->_linkTypeProvider->getLinkTypes() as $linkTypeName => $linkTypeId) {
                $linkIdColPrefix[$linkTypeId] = '_' . $linkTypeName . '_';
            }

            $this->rowCustomizer->prepareData($this->_entityCollection, $productIds);

            // prepare custom options information
            $customOptionsData = [];
            $customOptionsDataPre = [];

            foreach ($this->_storeIdToCode as $storeId => &$storeCode) {
                $options = $this->_optionColFactory->create()->reset()->addTitleToResult(
                    $storeId
                )->addPriceToResult(
                    $storeId
                )->addProductToFilter(
                    $productIds
                )->addValuesToResult(
                    $storeId
                );

                foreach ($options as $option) {
                    $row = [];
                    $productId = $option['product_id'];
                    $optionId = $option['option_id'];
                    $customOptions = isset(
                        $customOptionsDataPre[$productId][$optionId]
                    ) ? $customOptionsDataPre[$productId][$optionId] : [];

                    if ($defaultStoreId == $storeId) {
                        $row['_custom_option_type'] = $option['type'];
                        $row['_custom_option_title'] = $option['title'];
                        $row['_custom_option_is_required'] = $option['is_require'];
                        $row['_custom_option_price'] = $option['price'] . ($option['price_type'] ==
                            'percent' ? '%' : '');
                        $row['_custom_option_sku'] = $option['sku'];
                        $row['_custom_option_max_characters'] = $option['max_characters'];
                        $row['_custom_option_sort_order'] = $option['sort_order'];

                        // remember default title for later comparisons
                        $defaultTitles[$option['option_id']] = $option['title'];
                    } else {
                        $row['_custom_option_title'] = $option['title'];
                    }
                    $values = $option->getValues();
                    if ($values) {
                        $firstValue = array_shift($values);
                        $priceType = $firstValue['price_type'] == 'percent' ? '%' : '';

                        if ($defaultStoreId == $storeId) {
                            $row['_custom_option_row_title'] = $firstValue['title'];
                            $row['_custom_option_row_price'] = $firstValue['price'] . $priceType;
                            $row['_custom_option_row_sku'] = $firstValue['sku'];
                            $row['_custom_option_row_sort'] = $firstValue['sort_order'];

                            $defaultValueTitles[$firstValue['option_type_id']] = $firstValue['title'];
                        } else {
                            $row['_custom_option_row_title'] = $firstValue['title'];
                        }
                    }
                    if ($row) {
                        if ($defaultStoreId != $storeId) {
                            $row['_custom_option_store'] = $this->_storeIdToCode[$storeId];
                        }
                        $customOptionsDataPre[$productId][$optionId][] = $row;
                    }
                    foreach ($values as $value) {
                        $row = [];
                        $valuePriceType = $value['price_type'] == 'percent' ? '%' : '';

                        if ($defaultStoreId == $storeId) {
                            $row['_custom_option_row_title'] = $value['title'];
                            $row['_custom_option_row_price'] = $value['price'] . $valuePriceType;
                            $row['_custom_option_row_sku'] = $value['sku'];
                            $row['_custom_option_row_sort'] = $value['sort_order'];
                        } else {
                            $row['_custom_option_row_title'] = $value['title'];
                        }
                        if ($row) {
                            if ($defaultStoreId != $storeId) {
                                $row['_custom_option_store'] = $this->_storeIdToCode[$storeId];
                            }
                            $customOptionsDataPre[$option['product_id']][$option['option_id']][] = $row;
                        }
                    }
                    $option = null;
                }
                $options = null;
            }
            foreach ($customOptionsDataPre as $productId => &$optionsData) {
                $customOptionsData[$productId] = [];

                foreach ($optionsData as $optionId => &$optionRows) {
                    $customOptionsData[$productId] = array_merge($customOptionsData[$productId], $optionRows);
                }
                unset($optionRows, $optionsData);
            }
            unset($customOptionsDataPre);

            $this->_setHeaderColumns($customOptionsData, $stockItemRows);
            $this->_headerColumns = $this->rowCustomizer->addHeaderColumns($this->_headerColumns);

            foreach ($dataRows as $productId => &$productData) {
                foreach ($productData as $storeId => &$dataRow) {
                    if ($defaultStoreId != $storeId) {
                        $dataRow[self::COL_SKU] = null;
                        $dataRow[self::COL_ATTR_SET] = null;
                        $dataRow[self::COL_TYPE] = null;
                        if (isset($productData[$defaultStoreId][self::COL_VISIBILITY])) {
                            $dataRow[self::COL_VISIBILITY] = $productData[$defaultStoreId][self::COL_VISIBILITY];
                        }
                    } else {
                        $dataRow[self::COL_STORE] = null;
                        if (isset($stockItemRows[$productId])) {
                            $dataRow = array_merge($dataRow, $stockItemRows[$productId]);
                        }
                    }

                    $this->_updateDataWithCategoryColumns($dataRow, $rowCategories, $productId);
                    if ($rowWebsites[$productId]) {
                        $dataRow['_product_websites'] = $this->_websiteIdToCode[array_shift($rowWebsites[$productId])];
                    }
                    if (!empty($rowTierPrices[$productId])) {
                        $dataRow = array_merge($dataRow, array_shift($rowTierPrices[$productId]));
                    }
                    if (!empty($rowGroupPrices[$productId])) {
                        $dataRow = array_merge($dataRow, array_shift($rowGroupPrices[$productId]));
                    }
                    if (!empty($mediaGalery[$productId])) {
                        $dataRow = array_merge($dataRow, array_shift($mediaGalery[$productId]));
                    }
                    foreach ($linkIdColPrefix as $linkId => &$colPrefix) {
                        if (!empty($linksRows[$productId][$linkId])) {
                            $linkData = array_shift($linksRows[$productId][$linkId]);
                            $dataRow[$colPrefix . 'position'] = $linkData['position'];
                            $dataRow[$colPrefix . 'sku'] = $linkData['sku'];

                            if (null !== $linkData['default_qty']) {
                                $dataRow[$colPrefix . 'default_qty'] = $linkData['default_qty'];
                            }
                        }
                    }
                    if (!empty($customOptionsData[$productId])) {
                        $dataRow = array_merge($dataRow, array_shift($customOptionsData[$productId]));
                    }
                    $dataRow = $this->rowCustomizer->addData($dataRow, $productId);
                    if (!empty($rowMultiselects[$storeId][$productId])) {
                        foreach ($rowMultiselects[$storeId][$productId] as $attrKey => $attrVal) {
                            if (!empty($rowMultiselects[$storeId][$productId][$attrKey])) {
                                $dataRow[$attrKey] = array_shift($rowMultiselects[$storeId][$productId][$attrKey]);
                            }
                        }
                    }
                    $exportData[] = $dataRow;

                    // calculate largest links block
                    $largestLinks = 0;

                    if (isset($linksRows[$productId])) {
                        $linksRowsKeys = array_keys($linksRows[$productId]);
                        foreach ($linksRowsKeys as $linksRowsKey) {
                            $largestLinks = max($largestLinks, count($linksRows[$productId][$linksRowsKey]));
                        }
                    }
                    $additionalRowsCount = max(
                        count($rowCategories[$productId]),
                        count($rowWebsites[$productId]),
                        $largestLinks
                    );
                    if (!empty($rowTierPrices[$productId])) {
                        $additionalRowsCount = max($additionalRowsCount, count($rowTierPrices[$productId]));
                    }
                    if (!empty($rowGroupPrices[$productId])) {
                        $additionalRowsCount = max($additionalRowsCount, count($rowGroupPrices[$productId]));
                    }
                    if (!empty($mediaGalery[$productId])) {
                        $additionalRowsCount = max($additionalRowsCount, count($mediaGalery[$productId]));
                    }
                    if (!empty($customOptionsData[$productId])) {
                        $additionalRowsCount = max($additionalRowsCount, count($customOptionsData[$productId]));
                    }
                    $additionalRowsCount = $this->rowCustomizer
                        ->getAdditionalRowsCount($additionalRowsCount, $productId);
                    if (!empty($rowMultiselects[$storeId][$productId])) {
                        foreach ($rowMultiselects[$storeId][$productId] as $attributes) {
                            $additionalRowsCount = max($additionalRowsCount, count($attributes));
                        }
                    }

                    if ($additionalRowsCount) {
                        for ($i = 0; $i < $additionalRowsCount; $i++) {
                            $dataRow = [];
                            if ($defaultStoreId != $storeId) {
                                $dataRow[self::COL_STORE] = $this->_storeIdToCode[$storeId];
                            }
                            $this->_updateDataWithCategoryColumns($dataRow, $rowCategories, $productId);
                            if ($rowWebsites[$productId]) {
                                $dataRow['_product_websites'] = $this->_websiteIdToCode[array_shift(
                                    $rowWebsites[$productId]
                                )];
                            }
                            if (!empty($rowTierPrices[$productId])) {
                                $dataRow = array_merge($dataRow, array_shift($rowTierPrices[$productId]));
                            }
                            if (!empty($rowGroupPrices[$productId])) {
                                $dataRow = array_merge($dataRow, array_shift($rowGroupPrices[$productId]));
                            }
                            if (!empty($mediaGalery[$productId])) {
                                $dataRow = array_merge($dataRow, array_shift($mediaGalery[$productId]));
                            }
                            foreach ($linkIdColPrefix as $linkId => &$colPrefix) {
                                if (!empty($linksRows[$productId][$linkId])) {
                                    $linkData = array_shift($linksRows[$productId][$linkId]);
                                    $dataRow[$colPrefix . 'position'] = $linkData['position'];
                                    $dataRow[$colPrefix . 'sku'] = $linkData['sku'];

                                    if (null !== $linkData['default_qty']) {
                                        $dataRow[$colPrefix . 'default_qty'] = $linkData['default_qty'];
                                    }
                                }
                            }
                            if (!empty($customOptionsData[$productId])) {
                                $dataRow = array_merge($dataRow, array_shift($customOptionsData[$productId]));
                            }
                            $dataRow = $this->rowCustomizer->addData($dataRow, $productId);
                            if (!empty($rowMultiselects[$storeId][$productId])) {
                                foreach ($rowMultiselects[$storeId][$productId] as $attrKey => $attrVal) {
                                    if (!empty($rowMultiselects[$storeId][$productId][$attrKey])) {
                                        $dataRow[$attrKey] = array_shift(
                                            $rowMultiselects[$storeId][$productId][$attrKey]
                                        );
                                    }
                                }
                            }
                            $exportData[] = $dataRow;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    public function filterAttributeCollection(\Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection)
    {
        $validTypes = array_keys($this->_productTypeModels);

        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->_bannedAttributes)) {
                $collection->removeItemByKey($attribute->getId());
                continue;
            }
            $attrApplyTo = $attribute->getApplyTo();
            $attrApplyTo = $attrApplyTo ? array_intersect($attrApplyTo, $validTypes) : $validTypes;

            if ($attrApplyTo) {
                foreach ($attrApplyTo as $productType) {
                    // override attributes by its product type model
                    if ($this->_productTypeModels[$productType]->overrideAttribute($attribute)) {
                        break;
                    }
                }
            } else {
                // remove attributes of not-supported product types
                $collection->removeItemByKey($attribute->getId());
            }
        }
        return $collection;
    }

    /**
     * Entity attributes collection getter.
     *
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        return $this->_attributeColFactory->create();
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'catalog_product';
    }

    /**
     * Initialize attribute option values and types.
     *
     * @return $this
     */
    protected function _initAttributes()
    {
        foreach ($this->getAttributeCollection() as $attribute) {
            $this->_attributeValues[$attribute->getAttributeCode()] = $this->getAttributeOptions($attribute);
            $this->_attributeTypes[$attribute->getAttributeCode()] =
                \Magento\ImportExport\Model\Import::getAttributeType($attribute);
        }
        return $this;
    }
}
