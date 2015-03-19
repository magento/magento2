<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

use \Magento\Store\Model\Store;

/**
 * Export entity product model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var array
     */
    protected $collectedMultiselectsData = [];

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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

        $this->initTypeModels()
            ->initAttributes()
            ->_initStores()
            ->initAttributeSets()
            ->initWebsites()
            ->initCategories();
    }

    /**
     * Initialize attribute sets code-to-id pairs.
     *
     * @return $this
     */
    protected function initAttributeSets()
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
    protected function initCategories()
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    protected function initTypeModels()
    {
        $productTypes = $this->_exportConfig->getEntityTypes($this->getEntityTypeCode());
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            if (!($model = $this->_typeFactory->create($productTypeConfig['model']))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Entity type model \'%1\' is not found', $productTypeConfig['model'])
                );
            }
            if (!$model instanceof \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType) {
                throw new \Magento\Framework\Exception\LocalizedException(
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There are no product types available for export')
            );
        }
        $this->_disabledAttrs = array_unique($this->_disabledAttrs);

        return $this;
    }

    /**
     * Initialize website values.
     *
     * @return $this
     */
    protected function initWebsites()
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
    protected function prepareTierPrices(array $productIds)
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
    protected function prepareGroupPrices(array $productIds)
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
    protected function getMediaGallery(array $productIds)
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
    protected function prepareCatalogInventory(array $productIds)
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
    protected function prepareLinks(array $productIds)
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
    protected function updateDataWithCategoryColumns(&$dataRow, &$rowCategories, $productId)
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
    protected function setHeaderColumns($customOptionsData, $stockItemRows)
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
    protected function getItemsPerPage()
    {
        if ($this->_itemsPerPage === null) {
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
    protected function paginateCollection($page, $pageSize)
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
        $this->_getEntityCollection()->setStoreId(Store::DEFAULT_STORE_ID);
        $writer = $this->getWriter();
        $page = 0;
        while (true) {
            ++$page;
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($this->_getEntityCollection()->count() == 0) {
                break;
            }
            $exportData = $this->getExportData();
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getExportData()
    {
        $exportData = [];
        try {
            $rawData = $this->collectRawData();
            $multirawData = $this->collectMultirawData();

            $productIds = array_keys($rawData);
            $stockItemRows = $this->prepareCatalogInventory($productIds);

            $this->rowCustomizer->prepareData($this->_getEntityCollection(), $productIds);

            $this->setHeaderColumns($multirawData['customOptionsData'], $stockItemRows);
            $this->_headerColumns = $this->rowCustomizer->addHeaderColumns($this->_headerColumns);

            foreach ($rawData as $productId => $productData) {
                foreach ($productData as $storeId => $dataRow) {
                    if ($storeId == Store::DEFAULT_STORE_ID && isset($stockItemRows[$productId])) {
                        $dataRow = array_merge($dataRow, $stockItemRows[$productId]);
                    }

                    $exportData = array_merge($exportData, $this->addMultirowData($dataRow, $multirawData));
                }
            }
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        return $exportData;
    }

    /**
     * Collect export data for all products
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function collectRawData()
    {
        $data = [];
        $collection = $this->_getEntityCollection();
        foreach ($this->_storeIdToCode as $storeId => $storeCode) {
            $collection->setStoreId($storeId);
            /**
             * @var int $itemId
             * @var \Magento\Catalog\Model\Product $item
             */
            foreach ($collection as $itemId => $item) {
                foreach ($this->_getExportAttrCodes() as $code) {
                    $attrValue = $item->getData($code);
                    if (!$this->isValidAttributeValue($code, $attrValue)) {
                        continue;
                    }

                    if (isset($this->_attributeValues[$code][$attrValue]) && !empty($this->_attributeValues[$code])) {
                        $attrValue = $this->_attributeValues[$code][$attrValue];
                    }

                    if ($storeId != Store::DEFAULT_STORE_ID
                        && isset($data[$itemId][Store::DEFAULT_STORE_ID][$code])
                        && $data[$itemId][Store::DEFAULT_STORE_ID][$code] == $attrValue
                    ) {
                        continue;
                    }

                    if ($this->_attributeTypes[$code] !== 'multiselect') {
                        if (is_scalar($attrValue)) {
                            $data[$itemId][$storeId][$code] = $attrValue;
                        }
                    } else {
                        $this->collectMultiselectValues($item, $code, $storeId);
                    }
                }

                if (!empty($data[$itemId][$storeId]) || $this->hasMultiselectData($item, $storeId)) {
                    $attrSetId = $item->getAttributeSetId();
                    $data[$itemId][$storeId][self::COL_STORE] = $storeCode;
                    $data[$itemId][$storeId][self::COL_ATTR_SET] = $this->_attrSetIdToName[$attrSetId];
                    $data[$itemId][$storeId][self::COL_TYPE] = $item->getTypeId();
                }
                $data[$itemId][$storeId]['store_id'] = $storeId;
                $data[$itemId][$storeId]['product_id'] = $itemId;
            }
            $collection->clear();
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function collectMultirawData()
    {
        $data = [];
        $productIds = [];
        $rowWebsites = [];
        $rowCategories = [];

        $collection = $this->_getEntityCollection();
        $collection->setStoreId(Store::DEFAULT_STORE_ID);
        $collection->addCategoryIds()->addWebsiteNamesToResult();
        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($collection as $item) {
            $productIds[] = $item->getId();
            $rowWebsites[$item->getId()] = array_intersect(
                array_keys($this->_websiteIdToCode),
                $item->getWebsites()
            );
            $rowCategories[$item->getId()] = $item->getCategoryIds();
        }
        $collection->clear();

        $allCategoriesIds = array_merge(array_keys($this->_categories), array_keys($this->_rootCategories));
        foreach ($rowCategories as &$categories) {
            $categories = array_intersect($categories, $allCategoriesIds);
        }

        $data['rowWebsites'] = $rowWebsites;
        $data['rowCategories'] = $rowCategories;
        $data['mediaGalery'] = $this->getMediaGallery($productIds);
        $data['rowTierPrices'] = $this->prepareTierPrices($productIds);
        $data['rowGroupPrices'] = $this->prepareGroupPrices($productIds);
        $data['linksRows'] = $this->prepareLinks($productIds);

        $data['customOptionsData'] = $this->getCustomOptionsData($productIds);

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     * @param int $storeId
     * @return bool
     */
    protected function hasMultiselectData($item, $storeId)
    {
        return !empty($this->collectedMultiselectsData[$storeId][$item->getId()]);
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     * @param string $attrCode
     * @param int $storeId
     * @return $this
     */
    protected function collectMultiselectValues($item, $attrCode, $storeId)
    {
        $attrValue = $item->getData($attrCode);
        $optionIds = explode(',', $attrValue);
        $options = array_intersect_key(
            $this->_attributeValues[$attrCode],
            array_flip($optionIds)
        );
        if (!(isset($this->collectedMultiselectsData[Store::DEFAULT_STORE_ID][$item->getId()][$attrCode])
            && $this->collectedMultiselectsData[Store::DEFAULT_STORE_ID][$item->getId()][$attrCode] == $options)
        ) {
            $this->collectedMultiselectsData[$storeId][$item->getId()][$attrCode] = $options;
        }

        return $this;
    }

    /**
     * @param string $code
     * @param mixed $value
     * @return bool
     */
    protected function isValidAttributeValue($code, $value)
    {
        $isValid = true;
        if (!is_numeric($value) && empty($value)) {
            $isValid = false;
        }

        if (!isset($this->_attributeValues[$code])) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param array $dataRow
     * @param array $multirawData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function addMultirowData($dataRow, $multirawData)
    {
        $result = [];
        $productId = $dataRow['product_id'];
        $storeId = $dataRow['store_id'];

        unset($dataRow['product_id']);
        unset($dataRow['store_id']);

        while (true) {
            if (Store::DEFAULT_STORE_ID == $storeId) {
                unset($dataRow[self::COL_STORE]);
                $this->updateDataWithCategoryColumns($dataRow, $multirawData['rowCategories'], $productId);
                if (!empty($multirawData['rowWebsites'][$productId])) {
                    $dataRow['_product_websites'] = $this->_websiteIdToCode[
                        array_shift($multirawData['rowWebsites'][$productId])
                    ];
                }
                if (!empty($multirawData['rowTierPrices'][$productId])) {
                    $dataRow = array_merge($dataRow, array_shift($multirawData['rowTierPrices'][$productId]));
                }
                if (!empty($multirawData['rowGroupPrices'][$productId])) {
                    $dataRow = array_merge($dataRow, array_shift($multirawData['rowGroupPrices'][$productId]));
                }
                if (!empty($multirawData['mediaGalery'][$productId])) {
                    $dataRow = array_merge($dataRow, array_shift($multirawData['mediaGalery'][$productId]));
                }
                foreach ($this->_linkTypeProvider->getLinkTypes() as $linkTypeName => $linkId) {
                    if (!empty($multirawData['linksRows'][$productId][$linkId])) {
                        $colPrefix = '_' . $linkTypeName . '_';

                        $linkData = array_shift($multirawData['linksRows'][$productId][$linkId]);
                        $dataRow[$colPrefix . 'position'] = $linkData['position'];
                        $dataRow[$colPrefix . 'sku'] = $linkData['sku'];

                        if ($linkData['default_qty'] !== null) {
                            $dataRow[$colPrefix . 'default_qty'] = $linkData['default_qty'];
                        }
                    }
                }
                $dataRow = $this->rowCustomizer->addData($dataRow, $productId);

                if (!empty($multirawData['customOptionsData'][$productId])) {
                    $dataRow = array_merge($dataRow, array_shift($multirawData['customOptionsData'][$productId]));
                }
            }

            if (!empty($this->collectedMultiselectsData[$storeId][$productId])) {
                foreach (array_keys($this->collectedMultiselectsData[$storeId][$productId]) as $attrKey) {
                    if (!empty($this->collectedMultiselectsData[$storeId][$productId][$attrKey])) {
                        $dataRow[$attrKey] = array_shift(
                            $this->collectedMultiselectsData[$storeId][$productId][$attrKey]
                        );
                    }
                }
            }

            if (empty($dataRow)) {
                break;
            } elseif ($storeId != Store::DEFAULT_STORE_ID) {
                $dataRow[self::COL_STORE] = $this->_storeIdToCode[$storeId];
                $dataRow[self::COL_SKU] = null;
                $dataRow[self::COL_ATTR_SET] = null;
                $dataRow[self::COL_TYPE] = null;
                if (isset($productData[Store::DEFAULT_STORE_ID][self::COL_VISIBILITY])) {
                    $dataRow[self::COL_VISIBILITY] = $productData[Store::DEFAULT_STORE_ID][self::COL_VISIBILITY];
                }
            }

            $result[] = $dataRow;
            $dataRow = [];
        }

        return $result;
    }

    /**
     * @param int[] $productIds
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getCustomOptionsData($productIds)
    {
        $customOptionsData = [];
        $customOptionsDataPre = [];

        foreach (array_keys($this->_storeIdToCode) as $storeId) {
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

                if (Store::DEFAULT_STORE_ID == $storeId) {
                    $row['_custom_option_type'] = $option['type'];
                    $row['_custom_option_title'] = $option['title'];
                    $row['_custom_option_is_required'] = $option['is_require'];
                    $row['_custom_option_price'] = $option['price'] . ($option['price_type'] == 'percent' ? '%' : '');
                    $row['_custom_option_sku'] = $option['sku'];
                    $row['_custom_option_max_characters'] = $option['max_characters'];
                    $row['_custom_option_sort_order'] = $option['sort_order'];
                } else {
                    $row['_custom_option_title'] = $option['title'];
                }
                $values = $option->getValues();
                if ($values) {
                    $firstValue = array_shift($values);
                    $priceType = $firstValue['price_type'] == 'percent' ? '%' : '';

                    if (Store::DEFAULT_STORE_ID == $storeId) {
                        $row['_custom_option_row_title'] = $firstValue['title'];
                        $row['_custom_option_row_price'] = $firstValue['price'] . $priceType;
                        $row['_custom_option_row_sku'] = $firstValue['sku'];
                        $row['_custom_option_row_sort'] = $firstValue['sort_order'];
                    } else {
                        $row['_custom_option_row_title'] = $firstValue['title'];
                    }
                }

                if (Store::DEFAULT_STORE_ID != $storeId) {
                    $row['_custom_option_store'] = $this->_storeIdToCode[$storeId];
                }
                $customOptionsDataPre[$productId][$optionId][] = $row;

                foreach ($values as $value) {
                    $row = [];
                    $valuePriceType = $value['price_type'] == 'percent' ? '%' : '';

                    if (Store::DEFAULT_STORE_ID == $storeId) {
                        $row['_custom_option_row_title'] = $value['title'];
                        $row['_custom_option_row_price'] = $value['price'] . $valuePriceType;
                        $row['_custom_option_row_sku'] = $value['sku'];
                        $row['_custom_option_row_sort'] = $value['sort_order'];
                    } else {
                        $row['_custom_option_row_title'] = $value['title'];
                    }
                    if ($row) {
                        if (Store::DEFAULT_STORE_ID != $storeId) {
                            $row['_custom_option_store'] = $this->_storeIdToCode[$storeId];
                        }
                        $customOptionsDataPre[$option['product_id']][$optionId][] = $row;
                    }
                }
                $option = null;
            }
            $options = null;
        }

        foreach ($customOptionsDataPre as $productId => $optionsData) {
            $customOptionsData[$productId] = [];
            foreach ($optionsData as $optionId => $optionRows) {
                $customOptionsData[$productId] = array_merge(
                    $customOptionsData[$productId],
                    $optionRows
                );
            }
        }

        return $customOptionsData;
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
    protected function initAttributes()
    {
        foreach ($this->getAttributeCollection() as $attribute) {
            $this->_attributeValues[$attribute->getAttributeCode()] = $this->getAttributeOptions($attribute);
            $this->_attributeTypes[$attribute->getAttributeCode()] =
                \Magento\ImportExport\Model\Import::getAttributeType($attribute);
        }
        return $this;
    }
}
