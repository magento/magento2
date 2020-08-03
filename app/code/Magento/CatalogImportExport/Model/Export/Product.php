<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

use Magento\Catalog\Model\Product as ProductEntity;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;

/**
 * Export entity product model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @since 100.0.2
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

    const COL_PRODUCT_WEBSITES = '_product_websites';

    const COL_CATEGORY = '_category';

    const COL_ROOT_CATEGORY = '_root_category';

    const COL_SKU = 'sku';

    const COL_VISIBILITY = 'visibility';

    const COL_MEDIA_IMAGE = '_media_image';

    const COL_ADDITIONAL_ATTRIBUTES = 'additional_attributes';

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
     * Attributes defined by user
     *
     * @var array
     */
    private $userDefinedAttributes = [];

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_entityCollectionFactory;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_entityCollection;

    /**
     * Items per page for collection limitation
     *
     * @var int|null
     */
    protected $_itemsPerPage = null;

    /**
     * Header columns for export file
     *
     * @var array
     * @deprecated 100.2.0
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
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $_attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $_categoryColFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceModel;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var Collection
     */
    protected $_optionColFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
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
     * Map between import file fields and system fields/attributes
     *
     * @var array
     */
    protected $_fieldsMap = [
        'image' => 'base_image',
        'image_label' => "base_image_label",
        'thumbnail' => 'thumbnail_image',
        'thumbnail_label' => 'thumbnail_image_label',
        self::COL_MEDIA_IMAGE => 'additional_images',
        '_media_image_label' => 'additional_image_labels',
        self::COL_STORE => 'store_view_code',
        self::COL_ATTR_SET => 'attribute_set_code',
        self::COL_TYPE => 'product_type',
        self::COL_CATEGORY => 'categories',
        self::COL_PRODUCT_WEBSITES => 'product_websites',
        'status' => 'product_online',
        'news_from_date' => 'new_from_date',
        'news_to_date' => 'new_to_date',
        'options_container' => 'display_product_options_in',
        'minimal_price' => 'map_price',
        'msrp' => 'msrp_price',
        'msrp_enabled' => 'map_enabled',
        'special_from_date' => 'special_price_from_date',
        'special_to_date' => 'special_price_to_date',
        'min_qty' => 'out_of_stock_qty',
        'backorders' => 'allow_backorders',
        'min_sale_qty' => 'min_cart_qty',
        'max_sale_qty' => 'max_cart_qty',
        'notify_stock_qty' => 'notify_on_stock_below',
        'meta_keyword' => 'meta_keywords',
        'tax_class_id' => 'tax_class_name',
    ];

    /**
     * Attributes codes which shows as date
     *
     * @var array
     * @since 100.1.2
     */
    protected $dateAttrCodes = [
        'special_from_date',
        'special_to_date',
        'news_from_date',
        'news_to_date',
        'custom_design_from',
        'custom_design_to'
    ];

    /**
     * Attributes codes which are appropriate for export and not the part of additional_attributes.
     *
     * @var array
     */
    protected $_exportMainAttrCodes = [
        self::COL_SKU,
        'name',
        'description',
        'short_description',
        'weight',
        'product_online',
        'tax_class_name',
        'visibility',
        'price',
        'special_price',
        'special_price_from_date',
        'special_price_to_date',
        'url_key',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'base_image',
        'base_image_label',
        'small_image',
        'small_image_label',
        'thumbnail_image',
        'thumbnail_image_label',
        'swatch_image',
        'swatch_image_label',
        'created_at',
        'updated_at',
        'new_from_date',
        'new_to_date',
        'display_product_options_in',
        'map_price',
        'msrp_price',
        'map_enabled',
        'special_price_from_date',
        'special_price_to_date',
        'gift_message_available',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'product_options_container',
        'msrp_price',
        'msrp_display_actual_price_type',
        'map_enabled',
        'country_of_manufacture',
        'map_price',
        'display_product_options_in',
    ];

    /**
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
     * @var ProductFilterInterface
     */
    private $filter;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeColFactory
     * @param Product\Type\Factory $_typeFactory
     * @param ProductEntity\LinkTypeProvider $linkTypeProvider
     * @param RowCustomizerInterface $rowCustomizer
     * @param array $dateAttrCodes
     * @param ProductFilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer,
        array $dateAttrCodes = [],
        ?ProductFilterInterface $filter = null
    ) {
        $this->_entityCollectionFactory = $collectionFactory;
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
        $this->dateAttrCodes = array_merge($this->dateAttrCodes, $dateAttrCodes);
        $this->filter = $filter ?? ObjectManager::getInstance()->get(ProductFilterInterface::class);

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
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize = count($structure);
            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    $childCategory = $collection->getItemById($structure[$i]);
                    if ($childCategory) {
                        $name = $childCategory->getName();
                        $path[] = $this->quoteCategoryDelimiter($name);
                    }
                }
                $this->_rootCategories[$category->getId()] = array_shift($path);
                if ($pathSize > 2) {
                    $this->_categories[$category->getId()] = implode(CategoryProcessor::DELIMITER_CATEGORY, $path);
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
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $this->_disabledAttrs = array_merge($this->_disabledAttrs, $model->getDisabledAttrs());
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $this->_indexValueAttributes = array_merge(
                    $this->_indexValueAttributes,
                    $model->getIndexValueAttributes()
                );
            }
        }
        if (!$this->_productTypeModels) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There are no product types available for export.')
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

        $productEntityJoinField = $this->getProductEntityLinkField();

        $select = $this->_connection->select()->from(
            ['mgvte' => $this->_resourceModel->getTableName('catalog_product_entity_media_gallery_value_to_entity')],
            [
                "mgvte.$productEntityJoinField",
                'mgvte.value_id'
            ]
        )->joinLeft(
            ['mg' => $this->_resourceModel->getTableName('catalog_product_entity_media_gallery')],
            '(mg.value_id = mgvte.value_id)',
            [
                'mg.attribute_id',
                'filename' => 'mg.value',
            ]
        )->joinLeft(
            ['mgv' => $this->_resourceModel->getTableName('catalog_product_entity_media_gallery_value')],
            "(mg.value_id = mgv.value_id) and (mgvte.$productEntityJoinField = mgv.$productEntityJoinField)",
            [
                'mgv.label',
                'mgv.position',
                'mgv.disabled',
                'mgv.store_id',
            ]
        )->where(
            "mgvte.$productEntityJoinField IN (?)",
            $productIds
        );

        $rowMediaGallery = [];
        $stmt = $this->_connection->query($select);
        while ($mediaRow = $stmt->fetch()) {
            $rowMediaGallery[$mediaRow[$productEntityJoinField]][] = [
                '_media_attribute_id' => $mediaRow['attribute_id'],
                '_media_image' => $mediaRow['filename'],
                '_media_label' => $mediaRow['label'],
                '_media_position' => $mediaRow['position'],
                '_media_is_disabled' => $mediaRow['disabled'],
                '_media_store_id' => $mediaRow['store_id'],
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
        $select = $this->_connection->select()->from(
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
            $this->_connection->quoteInto(
                '(cpla.link_type_id = cpl.link_type_id AND cpla.product_link_attribute_code = ?)',
                'position'
            ),
            []
        )->joinLeft(
            ['cplaq' => $this->_resourceModel->getTableName('catalog_product_link_attribute')],
            $this->_connection->quoteInto(
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

        $stmt = $this->_connection->query($select);
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
     * @param array $dataRow
     * @param array $rowCategories
     * @param int $productId
     * @return bool
     */
    protected function updateDataWithCategoryColumns(&$dataRow, &$rowCategories, $productId)
    {
        if (!isset($rowCategories[$productId])) {
            return false;
        }
        $categories = [];
        foreach ($rowCategories[$productId] as $categoryId) {
            $categoryPath = $this->_rootCategories[$categoryId];
            if (isset($this->_categories[$categoryId])) {
                $categoryPath .= '/' . $this->_categories[$categoryId];
            }
            $categories[] = $categoryPath;
        }
        $dataRow[self::COL_CATEGORY] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $categories);
        unset($rowCategories[$productId]);

        return true;
    }

    /**
     * Get header columns
     *
     * @return string[]
     */
    public function _getHeaderColumns()
    {
        return $this->_customHeadersMapping($this->rowCustomizer->addHeaderColumns($this->_headerColumns));
    }

    /**
     * Return non-system attributes

     * @return array
     */
    private function getNonSystemAttributes(): array
    {
        $attrKeys = [];
        foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
            $attrKeys[] = $attribute->getAttributeCode();
        }

        return array_diff($this->_getExportMainAttrCodes(), $this->_customHeadersMapping($attrKeys));
    }

    /**
     * Set headers columns
     *
     * @param array $customOptionsData
     * @param array $stockItemRows
     * @return void
     * @deprecated 100.2.0 Logic will be moved to _getHeaderColumns in future release
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setHeaderColumns($customOptionsData, $stockItemRows)
    {
        $exportAttributes = (
            array_key_exists("skip_attr", $this->_parameters) && count($this->_parameters["skip_attr"])
        ) ?
            array_intersect(
                $this->_getExportMainAttrCodes(),
                array_merge(
                    $this->_customHeadersMapping($this->_getExportAttrCodes()),
                    $this->getNonSystemAttributes()
                )
            ) :
            $this->_getExportMainAttrCodes();

        if (!$this->_headerColumns) {
            $this->_headerColumns = array_merge(
                [
                    self::COL_SKU,
                    self::COL_STORE,
                    self::COL_ATTR_SET,
                    self::COL_TYPE,
                    self::COL_CATEGORY,
                    self::COL_PRODUCT_WEBSITES,
                ],
                $exportAttributes,
                [self::COL_ADDITIONAL_ATTRIBUTES],
                reset($stockItemRows) ? array_keys(end($stockItemRows)) : [],
                [
                    'related_skus',
                    'related_position',
                    'crosssell_skus',
                    'crosssell_position',
                    'upsell_skus',
                    'upsell_position',
                    'additional_images',
                    'additional_image_labels',
                    'hide_from_product_page',
                    'custom_options'
                ]
            );
        }
    }

    /**
     * Get attributes codes which are appropriate for export and not the part of additional_attributes.
     *
     * @return array
     */
    protected function _getExportMainAttrCodes()
    {
        return $this->_exportMainAttrCodes;
    }

    /**
     * Get entity collection
     *
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection($resetCollection = false)
    {
        if ($resetCollection || empty($this->_entityCollection)) {
            $this->_entityCollection = $this->_entityCollectionFactory->create();
        }
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
            $memoryLimitConfigValue = trim(ini_get('memory_limit'));
            $lastMemoryLimitLetter = strtolower($memoryLimitConfigValue[strlen($memoryLimitConfigValue) - 1]);
            $memoryLimit = (int) $memoryLimitConfigValue;
            switch ($lastMemoryLimitLetter) {
                case 'g':
                    $memoryLimit *= 1024;
                    // fall-through intentional
                    // no break
                case 'm':
                    $memoryLimit *= 1024;
                    // fall-through intentional
                    // no break
                case 'k':
                    $memoryLimit *= 1024;
                    break;
                default:
                    // minimum memory required by Magento
                    $memoryLimit = 250000000;
            }

            // Tested one product to have up to such size
            $memoryPerProduct = 500000;
            // Decrease memory limit to have supply
            $memoryUsagePercent = 0.8;
            // Minimum Products limit
            $minProductsLimit = 500;
            // Maximal Products limit
            $maxProductsLimit = 5000;

            $this->_itemsPerPage = (int)(
                ($memoryLimit * $memoryUsagePercent - memory_get_usage(true)) / $memoryPerProduct
            );
            if ($this->_itemsPerPage < $minProductsLimit) {
                $this->_itemsPerPage = $minProductsLimit;
            }
            if ($this->_itemsPerPage > $maxProductsLimit) {
                $this->_itemsPerPage = $maxProductsLimit;
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
     * @return string
     */
    public function export()
    {
        //Execution time may be very long
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);

        $writer = $this->getWriter();
        $page = 0;
        while (true) {
            ++$page;
            $entityCollection = $this->_getEntityCollection(true);
            $entityCollection->setOrder('entity_id', 'asc');
            $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);
            $this->_prepareEntityCollection($entityCollection);
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->count() == 0) {
                break;
            }
            $exportData = $this->getExportData();
            if ($page == 1) {
                $writer->setHeaderCols($this->_getHeaderColumns());
            }
            foreach ($exportData as $dataRow) {
                $writer->writeRow($this->_customFieldsMapping($dataRow));
            }
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }
        return $writer->getContents();
    }

    /**
     * Apply filter to collection and add not skipped attributes to select.
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @since 100.2.0
     */
    protected function _prepareEntityCollection(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection)
    {
        $exportFilter = !empty($this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP]) ?
            $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP] : [];

        $collection = $this->filter->filter($collection, $exportFilter);

        return parent::_prepareEntityCollection($collection);
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

            $this->rowCustomizer->prepareData(
                $this->_prepareEntityCollection($this->_entityCollectionFactory->create()),
                $productIds
            );

            $this->setHeaderColumns($multirawData['customOptionsData'], $stockItemRows);

            foreach ($rawData as $productId => $productData) {
                foreach ($productData as $storeId => $dataRow) {
                    if ($storeId == Store::DEFAULT_STORE_ID && isset($stockItemRows[$productId])) {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $dataRow = array_merge($dataRow, $stockItemRows[$productId]);
                    }
                    $this->appendMultirowData($dataRow, $multirawData);
                    if ($dataRow) {
                        $exportData[] = $dataRow;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }

    /**
     * Load products' data from the collection and filter it (if needed).
     *
     * @return array Keys are product IDs, values arrays with keys as store IDs
     *               and values as store-specific versions of Product entity.
     * @since 100.2.1
     */
    protected function loadCollection(): array
    {
        $data = [];
        $collection = $this->_getEntityCollection();
        foreach (array_keys($this->_storeIdToCode) as $storeId) {
            $collection->setOrder('entity_id', 'asc');
            $collection->setStoreId($storeId);
            $collection->load();
            foreach ($collection as $itemId => $item) {
                $data[$itemId][$storeId] = $item;
            }
            $collection->clear();
        }

        return $data;
    }

    /**
     * Collect export data for all products
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function collectRawData()
    {
        $data = [];
        $items = $this->loadCollection();

        /**
         * @var int $itemId
         * @var ProductEntity[] $itemByStore
         */
        foreach ($items as $itemId => $itemByStore) {
            foreach ($this->_storeIdToCode as $storeId => $storeCode) {
                $item = $itemByStore[$storeId];
                $additionalAttributes = [];
                $productLinkId = $item->getData($this->getProductEntityLinkField());
                foreach ($this->_getExportAttrCodes() as $code) {
                    $attrValue = $item->getData($code);
                    if (!$this->isValidAttributeValue($code, $attrValue)) {
                        continue;
                    }

                    if (isset($this->_attributeValues[$code][$attrValue]) && !empty($this->_attributeValues[$code])) {
                        $attrValue = $this->_attributeValues[$code][$attrValue];
                    }
                    $fieldName = isset($this->_fieldsMap[$code]) ? $this->_fieldsMap[$code] : $code;

                    if ($this->_attributeTypes[$code] == 'datetime') {
                        if (in_array($code, $this->dateAttrCodes)
                            || in_array($code, $this->userDefinedAttributes)
                        ) {
                            $attrValue = $this->_localeDate->formatDateTime(
                                new \DateTime($attrValue),
                                \IntlDateFormatter::SHORT,
                                \IntlDateFormatter::NONE,
                                null,
                                date_default_timezone_get()
                            );
                        } else {
                            $attrValue = $this->_localeDate->formatDateTime(
                                new \DateTime($attrValue),
                                \IntlDateFormatter::SHORT,
                                \IntlDateFormatter::SHORT
                            );
                        }
                    }

                    if ($storeId != Store::DEFAULT_STORE_ID
                        && isset($data[$itemId][Store::DEFAULT_STORE_ID][$fieldName])
                        && $data[$itemId][Store::DEFAULT_STORE_ID][$fieldName] == htmlspecialchars_decode($attrValue)
                    ) {
                        continue;
                    }

                    if ($this->_attributeTypes[$code] !== 'multiselect') {
                        if (is_scalar($attrValue)) {
                            if (!in_array($fieldName, $this->_getExportMainAttrCodes())) {
                                $additionalAttributes[$fieldName] = $fieldName .
                                    ImportProduct::PAIR_NAME_VALUE_SEPARATOR . $this->wrapValue($attrValue);
                            }
                            $data[$itemId][$storeId][$fieldName] = htmlspecialchars_decode($attrValue);
                        }
                    } else {
                        $this->collectMultiselectValues($item, $code, $storeId);
                        if (!empty($this->collectedMultiselectsData[$storeId][$productLinkId][$code])) {
                            $additionalAttributes[$code] = $fieldName .
                                ImportProduct::PAIR_NAME_VALUE_SEPARATOR . implode(
                                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                                    $this->wrapValue($this->collectedMultiselectsData[$storeId][$productLinkId][$code])
                                );
                        }
                    }
                }

                if (!empty($additionalAttributes)) {
                    $additionalAttributes = array_map('htmlspecialchars_decode', $additionalAttributes);
                    $data[$itemId][$storeId][self::COL_ADDITIONAL_ATTRIBUTES] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributes);
                } else {
                    unset($data[$itemId][$storeId][self::COL_ADDITIONAL_ATTRIBUTES]);
                }

                $attrSetId = $item->getAttributeSetId();
                $data[$itemId][$storeId][self::COL_STORE] = $storeCode;
                $data[$itemId][$storeId][self::COL_ATTR_SET] = $this->_attrSetIdToName[$attrSetId];
                $data[$itemId][$storeId][self::COL_TYPE] = $item->getTypeId();
                $data[$itemId][$storeId][self::COL_SKU] = htmlspecialchars_decode($item->getSku());
                $data[$itemId][$storeId]['store_id'] = $storeId;
                $data[$itemId][$storeId]['product_id'] = $itemId;
                $data[$itemId][$storeId]['product_link_id'] = $productLinkId;
            }
        }

        return $data;
    }
    //phpcs:enable Generic.Metrics.NestingLevel

    /**
     * Wrap values with double quotes if "Fields Enclosure" option is enabled
     *
     * @param string|array $value
     * @return string|array
     */
    private function wrapValue($value)
    {
        if (!empty($this->_parameters[\Magento\ImportExport\Model\Export::FIELDS_ENCLOSURE])) {
            $wrap = function ($value) {
                return sprintf('"%s"', str_replace('"', '""', $value));
            };

            $value = is_array($value) ? array_map($wrap, $value) : $wrap($value);
        }

        return $value;
    }

    /**
     * Collect multi raw data from
     *
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
            $productLinkIds[] = $item->getData($this->getProductEntityLinkField());
            $productIds[] = $item->getId();
            $rowWebsites[$item->getId()] = array_intersect(
                array_keys($this->_websiteIdToCode),
                $item->getWebsites()
            );
            $rowCategories[$item->getId()] = array_combine($item->getCategoryIds(), $item->getCategoryIds());
        }
        $collection->clear();

        $allCategoriesIds = array_merge(array_keys($this->_categories), array_keys($this->_rootCategories));
        $allCategoriesIds = array_combine($allCategoriesIds, $allCategoriesIds);
        foreach ($rowCategories as &$categories) {
            $categories = array_intersect_key($categories, $allCategoriesIds);
        }

        $data['rowWebsites'] = $rowWebsites;
        $data['rowCategories'] = $rowCategories;
        $data['mediaGalery'] = $this->getMediaGallery($productLinkIds);
        $data['linksRows'] = $this->prepareLinks($productLinkIds);

        $data['customOptionsData'] = $this->getCustomOptionsData($productLinkIds);

        return $data;
    }

    /**
     * Check the current data has multiselect value
     *
     * @param \Magento\Catalog\Model\Product $item
     * @param int $storeId
     * @return bool
     * @deprecated 100.2.3
     */
    protected function hasMultiselectData($item, $storeId)
    {
        $linkId = $item->getData($this->getProductEntityLinkField());
        return !empty($this->collectedMultiselectsData[$storeId][$linkId]);
    }

    /**
     * Collect multiselect values based on value
     *
     * @param \Magento\Catalog\Model\Product $item
     * @param string $attrCode
     * @param int $storeId
     * @return $this
     */
    protected function collectMultiselectValues($item, $attrCode, $storeId)
    {
        $attrValue = $item->getData($attrCode);
        $optionIds = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $attrValue);
        $options = array_intersect_key(
            $this->_attributeValues[$attrCode],
            array_flip($optionIds)
        );
        $linkId = $item->getData($this->getProductEntityLinkField());
        if (!(isset($this->collectedMultiselectsData[Store::DEFAULT_STORE_ID][$linkId][$attrCode])
            && $this->collectedMultiselectsData[Store::DEFAULT_STORE_ID][$linkId][$attrCode] == $options)
        ) {
            $this->collectedMultiselectsData[$storeId][$linkId][$attrCode] = $options;
        }

        return $this;
    }

    /**
     * Check attribute is valid.
     *
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

        if (is_array($value)) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Append multi row data
     *
     * @param array $dataRow
     * @param array $multiRawData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function appendMultirowData(&$dataRow, $multiRawData)
    {
        $productId = $dataRow['product_id'];
        $productLinkId = $dataRow['product_link_id'];
        $storeId = $dataRow['store_id'];
        $sku = $dataRow[self::COL_SKU];
        $type = $dataRow[self::COL_TYPE];
        $attributeSet = $dataRow[self::COL_ATTR_SET];

        unset($dataRow['product_id']);
        unset($dataRow['product_link_id']);
        unset($dataRow['store_id']);
        unset($dataRow[self::COL_SKU]);
        unset($dataRow[self::COL_STORE]);
        unset($dataRow[self::COL_ATTR_SET]);
        unset($dataRow[self::COL_TYPE]);

        if (Store::DEFAULT_STORE_ID == $storeId) {
            $this->updateDataWithCategoryColumns($dataRow, $multiRawData['rowCategories'], $productId);
            if (!empty($multiRawData['rowWebsites'][$productId])) {
                $websiteCodes = [];
                foreach ($multiRawData['rowWebsites'][$productId] as $productWebsite) {
                    $websiteCodes[] = $this->_websiteIdToCode[$productWebsite];
                }
                $dataRow[self::COL_PRODUCT_WEBSITES] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $websiteCodes);
                $multiRawData['rowWebsites'][$productId] = [];
            }
            if (!empty($multiRawData['mediaGalery'][$productLinkId])) {
                $additionalImages = [];
                $additionalImageLabels = [];
                $additionalImageIsDisabled = [];
                foreach ($multiRawData['mediaGalery'][$productLinkId] as $mediaItem) {
                    if ((int)$mediaItem['_media_store_id'] === Store::DEFAULT_STORE_ID) {
                        $additionalImages[] = $mediaItem['_media_image'];
                        $additionalImageLabels[] = $mediaItem['_media_label'];

                        if ($mediaItem['_media_is_disabled'] == true) {
                            $additionalImageIsDisabled[] = $mediaItem['_media_image'];
                        }
                    }
                }
                $dataRow['additional_images'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImages);
                $dataRow['additional_image_labels'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageLabels);
                $dataRow['hide_from_product_page'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageIsDisabled);
                $multiRawData['mediaGalery'][$productLinkId] = [];
            }
            foreach ($this->_linkTypeProvider->getLinkTypes() as $linkTypeName => $linkId) {
                if (!empty($multiRawData['linksRows'][$productLinkId][$linkId])) {
                    $colPrefix = $linkTypeName . '_';

                    $associations = [];
                    foreach ($multiRawData['linksRows'][$productLinkId][$linkId] as $linkData) {
                        if ($linkData['default_qty'] !== null) {
                            $skuItem = $linkData['sku'] . ImportProduct::PAIR_NAME_VALUE_SEPARATOR .
                                $linkData['default_qty'];
                        } else {
                            $skuItem = $linkData['sku'];
                        }
                        $associations[$skuItem] = $linkData['position'];
                    }
                    $multiRawData['linksRows'][$productLinkId][$linkId] = [];
                    asort($associations);
                    $dataRow[$colPrefix . 'skus'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_keys($associations));
                    $dataRow[$colPrefix . 'position'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_values($associations));
                }
            }
            $dataRow = $this->rowCustomizer->addData($dataRow, $productId);
        } else {
            $additionalImageIsDisabled = [];
            if (!empty($multiRawData['mediaGalery'][$productLinkId])) {
                foreach ($multiRawData['mediaGalery'][$productLinkId] as $mediaItem) {
                    if ((int)$mediaItem['_media_store_id'] === $storeId) {
                        if ($mediaItem['_media_is_disabled'] == true) {
                            $additionalImageIsDisabled[] = $mediaItem['_media_image'];
                        }
                    }
                }
            }
            if ($additionalImageIsDisabled) {
                $dataRow['hide_from_product_page'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageIsDisabled);
            }
        }

        if (!empty($this->collectedMultiselectsData[$storeId][$productId])) {
            foreach (array_keys($this->collectedMultiselectsData[$storeId][$productId]) as $attrKey) {
                if (!empty($this->collectedMultiselectsData[$storeId][$productId][$attrKey])) {
                    $dataRow[$attrKey] = implode(
                        Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        $this->collectedMultiselectsData[$storeId][$productId][$attrKey]
                    );
                }
            }
        }

        if (!empty($multiRawData['customOptionsData'][$productLinkId][$storeId])) {
            $shouldBeMerged = true;
            $customOptionsRows = $multiRawData['customOptionsData'][$productLinkId][$storeId];

            if ($storeId != Store::DEFAULT_STORE_ID
                && !empty($multiRawData['customOptionsData'][$productLinkId][Store::DEFAULT_STORE_ID])
            ) {
                $defaultCustomOptions = $multiRawData['customOptionsData'][$productLinkId][Store::DEFAULT_STORE_ID];
                if (!array_diff($defaultCustomOptions, $customOptionsRows)) {
                    $shouldBeMerged = false;
                }
            }

            if ($shouldBeMerged) {
                $multiRawData['customOptionsData'][$productLinkId][$storeId] = [];
                $customOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $customOptionsRows);
                $dataRow = array_merge($dataRow, ['custom_options' => $customOptions]);
            }
        }

        if (empty($dataRow)) {
            return null;
        } elseif ($storeId != Store::DEFAULT_STORE_ID) {
            $dataRow[self::COL_STORE] = $this->_storeIdToCode[$storeId];
        }
        $dataRow[self::COL_SKU] = $sku;
        $dataRow[self::COL_ATTR_SET] = $attributeSet;
        $dataRow[self::COL_TYPE] = $type;

        return $dataRow;
    }

    /**
     * Add multi row data to export
     *
     * @deprecated 100.1.0
     * @param array $dataRow
     * @param array $multiRawData
     * @return array
     */
    protected function addMultirowData($dataRow, $multiRawData)
    {
        $data = $this->appendMultirowData($dataRow, $multiRawData);
        return $data ? [$data] : [];
    }

    /**
     * Custom fields mapping for changed purposes of fields and field names
     *
     * @param array $rowData
     *
     * @return array
     */
    protected function _customFieldsMapping($rowData)
    {
        foreach ($this->_fieldsMap as $systemFieldName => $fileFieldName) {
            if (isset($rowData[$systemFieldName])) {
                $rowData[$fileFieldName] = $rowData[$systemFieldName];
                unset($rowData[$systemFieldName]);
            }
        }
        return $rowData;
    }

    /**
     * Custom headers mapping for changed field names
     *
     * @param array $rowData
     *
     * @return array
     */
    protected function _customHeadersMapping($rowData)
    {
        foreach ($rowData as $key => $fieldName) {
            if (isset($this->_fieldsMap[$fieldName])) {
                $rowData[$key] = $this->_fieldsMap[$fieldName];
            }
        }
        return $rowData;
    }

    /**
     * Convert option row to cell string
     *
     * @param array $option
     * @return string
     */
    protected function optionRowToCellString($option)
    {
        $result = [];

        foreach ($option as $key => $value) {
            $result[] = $key . ImportProduct::PAIR_NAME_VALUE_SEPARATOR . $value;
        }

        return implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $result);
    }

    /**
     * Collect custom options data for products that will be exported.
     *
     * Option name and type will be collected for all store views, all other data (which can't be changed on store view
     * level will be collected for DEFAULT_STORE_ID only.
     * Store view specified data will be saved to the additional store view row.
     *
     * @param int[] $productIds
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getCustomOptionsData($productIds)
    {
        $customOptionsData = [];
        $defaultOptionsData = [];

        foreach (array_keys($this->_storeIdToCode) as $storeId) {
            $options = $this->_optionColFactory->create();
            /* @var Collection $options*/
            $options->reset()
                ->addOrder('sort_order', Collection::SORT_ORDER_ASC)
                ->addTitleToResult($storeId)
                ->addPriceToResult($storeId)
                ->addProductToFilter($productIds)
                ->addValuesToResult($storeId);

            foreach ($options as $option) {
                $optionData = $option->toArray();
                $row = [];
                $productId = $option['product_id'];
                $row['name'] = $option['title'];
                $row['type'] = $option['type'];

                $row['required'] = $this->getOptionValue('is_require', $defaultOptionsData, $optionData);
                $row['price'] = $this->getOptionValue('price', $defaultOptionsData, $optionData);
                $row['sku'] = $this->getOptionValue('sku', $defaultOptionsData, $optionData);
                if (array_key_exists('max_characters', $optionData)
                    || array_key_exists('max_characters', $defaultOptionsData)
                ) {
                    $row['max_characters'] = $this->getOptionValue('max_characters', $defaultOptionsData, $optionData);
                }
                foreach (['file_extension', 'image_size_x', 'image_size_y'] as $fileOptionKey) {
                    if (isset($option[$fileOptionKey]) || isset($defaultOptionsData[$fileOptionKey])) {
                        $row[$fileOptionKey] = $this->getOptionValue($fileOptionKey, $defaultOptionsData, $optionData);
                    }
                }
                $percentType = $this->getOptionValue('price_type', $defaultOptionsData, $optionData);
                $row['price_type'] = ($percentType === 'percent') ? 'percent' : 'fixed';

                if (Store::DEFAULT_STORE_ID === $storeId) {
                    $optionId = $option['option_id'];
                    $defaultOptionsData[$optionId] = $option->toArray();
                }

                $values = $option->getValues();

                if ($values) {
                    foreach ($values as $value) {
                        $row['option_title'] = $value['title'];
                        $row['option_title'] = $value['title'];
                        $row['price'] = $value['price'];
                        $row['price_type'] = ($value['price_type'] === 'percent') ? 'percent' : 'fixed';
                        $row['sku'] = $value['sku'];
                        $customOptionsData[$productId][$storeId][] = $this->optionRowToCellString($row);
                    }
                } else {
                    $customOptionsData[$productId][$storeId][] = $this->optionRowToCellString($row);
                }
                $option = null;
            }
            $options = null;
        }

        return $customOptionsData;
    }

    /**
     * Get value for custom option according to store or default value
     *
     * @param string $optionName
     * @param array $defaultOptionsData
     * @param array $optionData
     * @return mixed
     */
    private function getOptionValue($optionName, $defaultOptionsData, $optionData)
    {
        $optionId = $optionData['option_id'];

        if (array_key_exists($optionName, $optionData) && $optionData[$optionName] !== null) {
            return $optionData[$optionName];
        }

        if (array_key_exists($optionId, $defaultOptionsData)
            && array_key_exists($optionName, $defaultOptionsData[$optionId])
        ) {
            return $defaultOptionsData[$optionId][$optionName];
        }

        return null;
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function filterAttributeCollection(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection)
    {
        $validTypes = array_keys($this->_productTypeModels);
        $validTypes = array_combine($validTypes, $validTypes);

        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->_bannedAttributes)) {
                $collection->removeItemByKey($attribute->getId());
                continue;
            }
            $attrApplyTo = $attribute->getApplyTo();
            $attrApplyTo = array_combine($attrApplyTo, $attrApplyTo);
            $attrApplyTo = $attrApplyTo ? array_intersect_key($attrApplyTo, $validTypes) : $validTypes;

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
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
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
            if ($attribute->getIsUserDefined()) {
                $this->userDefinedAttributes[] = $attribute->getAttributeCode();
            }
        }
        return $this;
    }

    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
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
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Quoting category delimiter character in string.
     *
     * @param string $string
     * @return string
     */
    private function quoteCategoryDelimiter($string)
    {
        return str_replace(
            CategoryProcessor::DELIMITER_CATEGORY,
            '\\' . CategoryProcessor::DELIMITER_CATEGORY,
            $string
        );
    }
}
