<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Import entity product model
 */
class Product extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const CONFIG_KEY_PRODUCT_TYPES = 'global/importexport/import_product_types';

    /**
     * Size of bunch - part of products to save in one step.
     */
    const BUNCH_SIZE = 20;

    /**
     * Value that means all entities (e.g. websites, groups etc.)
     */
    const VALUE_ALL = 'all';

    /**
     * Data row scopes.
     */
    const SCOPE_DEFAULT = 1;

    const SCOPE_WEBSITE = 2;

    const SCOPE_STORE = 0;

    const SCOPE_NULL = -1;

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

    /**
     * Error codes.
     */
    const ERROR_INVALID_SCOPE = 'invalidScope';

    const ERROR_INVALID_WEBSITE = 'invalidWebsite';

    const ERROR_INVALID_STORE = 'invalidStore';

    const ERROR_INVALID_ATTR_SET = 'invalidAttrSet';

    const ERROR_INVALID_TYPE = 'invalidType';

    const ERROR_INVALID_CATEGORY = 'invalidCategory';

    const ERROR_VALUE_IS_REQUIRED = 'isRequired';

    const ERROR_TYPE_CHANGED = 'typeChanged';

    const ERROR_SKU_IS_EMPTY = 'skuEmpty';

    const ERROR_NO_DEFAULT_ROW = 'noDefaultRow';

    const ERROR_CHANGE_TYPE = 'changeProductType';

    const ERROR_DUPLICATE_SCOPE = 'duplicateScope';

    const ERROR_DUPLICATE_SKU = 'duplicateSKU';

    const ERROR_CHANGE_ATTR_SET = 'changeAttrSet';

    const ERROR_TYPE_UNSUPPORTED = 'productTypeUnsupported';

    const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';

    const ERROR_INVALID_TIER_PRICE_QTY = 'invalidTierPriceOrQty';

    const ERROR_INVALID_TIER_PRICE_SITE = 'tierPriceWebsiteInvalid';

    const ERROR_INVALID_TIER_PRICE_GROUP = 'tierPriceGroupInvalid';

    const ERROR_TIER_DATA_INCOMPLETE = 'tierPriceDataIsIncomplete';

    const ERROR_INVALID_GROUP_PRICE_SITE = 'groupPriceWebsiteInvalid';

    const ERROR_INVALID_GROUP_PRICE_GROUP = 'groupPriceGroupInvalid';

    const ERROR_GROUP_PRICE_DATA_INCOMPLETE = 'groupPriceDataIsIncomplete';

    const ERROR_SKU_NOT_FOUND_FOR_DELETE = 'skuNotFoundToDelete';

    const ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND = 'superProductsSkuNotFound';

    const ERROR_MEDIA_DATA_INCOMPLETE = 'mediaDataIsIncomplete';

    const ERROR_INVALID_WEIGHT = 'invalidWeight';

    /**
     * Pairs of attribute set ID-to-name.
     *
     * @var array
     */
    protected $_attrSetIdToName = [];

    /**
     * Pairs of attribute set name-to-ID.
     *
     * @var array
     */
    protected $_attrSetNameToId = [];

    /**
     * Categories text-path to ID hash.
     *
     * @var array
     */
    protected $_categories = [];

    /**
     * Categories text-path to ID hash with roots checking.
     *
     * @var array
     */
    protected $_categoriesWithRoots = [];

    /**
     * Customer groups ID-to-name.
     *
     * @var array
     */
    protected $_customerGroups = [];

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
     * Links attribute name-to-link type ID.
     *
     * @var array
     */
    protected $_linkNameToId = [
        '_related_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
        '_crosssell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
        '_upsell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
    ];

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_INVALID_SCOPE => 'Invalid value in Scope column',
        self::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)',
        self::ERROR_INVALID_STORE => 'Invalid value in Store column (store does not exists?)',
        self::ERROR_INVALID_ATTR_SET => 'Invalid value for Attribute Set column (set does not exists?)',
        self::ERROR_INVALID_TYPE => 'Product Type is invalid or not supported',
        self::ERROR_INVALID_CATEGORY => 'Category does not exists',
        self::ERROR_VALUE_IS_REQUIRED => "Required attribute '%s' has an empty value",
        self::ERROR_TYPE_CHANGED => 'Trying to change type of existing products',
        self::ERROR_SKU_IS_EMPTY => 'SKU is empty',
        self::ERROR_NO_DEFAULT_ROW => 'Default values row does not exists',
        self::ERROR_CHANGE_TYPE => 'Product type change is not allowed',
        self::ERROR_DUPLICATE_SCOPE => 'Duplicate scope',
        self::ERROR_DUPLICATE_SKU => 'Duplicate SKU',
        self::ERROR_CHANGE_ATTR_SET => 'Product attribute set change is not allowed',
        self::ERROR_TYPE_UNSUPPORTED => 'Product type is not supported',
        self::ERROR_ROW_IS_ORPHAN => 'Orphan rows that will be skipped due default row errors',
        self::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid',
        self::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid',
        self::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group ID is invalid',
        self::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete',
        self::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found',
        self::ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND => 'Product with specified super products SKU not found',
        self::ERROR_MEDIA_DATA_INCOMPLETE => 'Media data is incomplete',
        self::ERROR_INVALID_WEIGHT => 'Product weight is invalid',
    ];

    /**
     * Dry-runned products information from import file.
     *
     * [SKU] => array(
     *     'type_id'        => (string) product type
     *     'attr_set_id'    => (int) product attribute set ID
     *     'entity_id'      => (int) product ID (value for new products will be set after entity save)
     *     'attr_set_code'  => (string) attribute set code
     * )
     *
     * @var array
     */
    protected $_newSku = [];

    /**
     * Existing products SKU-related information in form of array:
     *
     * [SKU] => array(
     *     'type_id'        => (string) product type
     *     'attr_set_id'    => (int) product attribute set ID
     *     'entity_id'      => (int) product ID
     *     'supported_type' => (boolean) is product type supported by current version of import module
     * )
     *
     * @var array
     */
    protected $_oldSku = [];

    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_specialAttributes = [
        '_store',
        '_attribute_set',
        '_type',
        self::COL_CATEGORY,
        self::COL_ROOT_CATEGORY,
        '_product_websites',
        '_tier_price_website',
        '_tier_price_customer_group',
        '_tier_price_qty',
        '_tier_price_price',
        '_related_sku',
        '_group_price_website',
        '_group_price_customer_group',
        '_group_price_price',
        '_related_position',
        '_crosssell_sku',
        '_crosssell_position',
        '_upsell_sku',
        '_upsell_position',
        '_custom_option_store',
        '_custom_option_type',
        '_custom_option_title',
        '_custom_option_is_required',
        '_custom_option_price',
        '_custom_option_sku',
        '_custom_option_max_characters',
        '_custom_option_sort_order',
        '_custom_option_file_extension',
        '_custom_option_image_size_x',
        '_custom_option_image_size_y',
        '_custom_option_row_title',
        '_custom_option_row_price',
        '_custom_option_row_sku',
        '_custom_option_row_sort',
        '_media_attribute_id',
        '_media_image',
        '_media_label',
        '_media_position',
        '_media_is_disabled',
    ];

    /**
     * @var array
     */
    protected $defaultStockData = [
        'manage_stock' => 1,
        'use_config_manage_stock' => 1,
        'qty' => 0,
        'min_qty' => 0,
        'use_config_min_qty' => 1,
        'min_sale_qty' => 1,
        'use_config_min_sale_qty' => 1,
        'max_sale_qty' => 10000,
        'use_config_max_sale_qty' => 1,
        'is_qty_decimal' => 0,
        'backorders' => 0,
        'use_config_backorders' => 1,
        'notify_stock_qty' => 1,
        'use_config_notify_stock_qty' => 1,
        'enable_qty_increments' => 0,
        'use_config_enable_qty_inc' => 1,
        'qty_increments' => 0,
        'use_config_qty_increments' => 1,
        'is_in_stock' => 1,
        'low_stock_date' => null,
        'stock_status_changed_auto' => 0,
        'is_decimal_divided' => 0,
    ];

    /**
     * Column names that holds images files names
     *
     * @var string[]
     */
    protected $_imagesArrayKeys = ['_media_image', 'image', 'small_image', 'thumbnail'];

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
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $_storeCodeToId = [];

    /**
     * Store ID to its website stores IDs.
     *
     * @var array
     */
    protected $_storeIdToWebsiteStoreIds = [];

    /**
     * Website code-to-ID
     *
     * @var array
     */
    protected $_websiteCodeToId = [];

    /**
     * Website code to store code-to-ID pairs which it consists.
     *
     * @var array
     */
    protected $_websiteCodeToStoreIds = [];

    /**
     * Media files uploader
     *
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $_fileUploader;

    /**
     * Import entity which provide import of product custom options
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product\Option
     */
    protected $_optionEntity;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\ImportExport\Model\Import\Config
     */
    protected $_importConfig;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $_categoryColFactory;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\Factory
     */
    protected $_productTypeFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\LinkFactory
     */
    protected $_linkFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory
     */
    protected $_proxyProdFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory
     */
    protected $_stockResItemFac;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * {@inheritdoc}
     */
    protected $masterAttributeCode = 'sku';

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\Resource\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ImportExport\Model\Import\Config $importConfig
     * @param Proxy\Product\ResourceFactory $resourceFactory
     * @param Product\OptionFactory $optionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Product\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\Resource\Product\LinkFactory $linkFactory
     * @param Proxy\ProductFactory $proxyProdFactory
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory $stockResItemFac
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Indexer\Model\IndexerRegistry $indexerRegistry
     * @param array $data
     * @throws \Magento\Framework\Model\Exception
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\Resource\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Resource $resource,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\Resource\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Indexer\Model\IndexerRegistry $indexerRegistry,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockState = $stockState;
        $this->_catalogData = $catalogData;
        $this->_importConfig = $importConfig;
        $this->_resourceFactory = $resourceFactory;
        $this->_setColFactory = $setColFactory;
        $this->_categoryColFactory = $categoryColFactory;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_linkFactory = $linkFactory;
        $this->_proxyProdFactory = $proxyProdFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_stockResItemFac = $stockResItemFac;
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->indexerRegistry = $indexerRegistry;
        $this->_logger = $logger;
        parent::__construct($coreData, $importExportData, $importData, $config, $resource, $resourceHelper, $string);
        $this->_optionEntity = isset(
            $data['option_entity']
        ) ? $data['option_entity'] : $optionFactory->create(
            ['data' => ['product_entity' => $this]]
        );

        $this->_initWebsites()
            ->_initStores()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initCategories()
            ->_initSkus()
            ->_initCustomerGroups();
    }

    /**
     * Retrieve instance of product custom options import entity
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Option
     */
    public function getOptionEntity()
    {
        return $this->_optionEntity;
    }

    /**
     * Set import parameters
     *
     * @param array $params
     * @return $this
     */
    public function setParameters(array $params)
    {
        parent::setParameters($params);
        $this->getOptionEntity()->setParameters($params);

        return $this;
    }

    /**
     * Delete products.
     *
     * @return $this
     */
    protected function _deleteProducts()
    {
        $productEntityTable = $this->_resourceFactory->create()->getEntityTable();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $idToDelete = [];

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    $idToDelete[] = $this->_oldSku[$rowData[self::COL_SKU]]['entity_id'];
                }
            }
            if ($idToDelete) {
                $this->_connection->query(
                    $this->_connection->quoteInto(
                        "DELETE FROM `{$productEntityTable}` WHERE `entity_id` IN (?)",
                        $idToDelete
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Create Product entity from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteProducts();
        } else {
            $this->_saveProducts();
            foreach ($this->_productTypeModels as $productTypeModel) {
                $productTypeModel->saveData();
            }
            $this->_saveLinks();
            $this->_saveStockItem();
            $this->getOptionEntity()->importData();
        }
        $this->_eventManager->dispatch('catalog_product_import_finish_before', ['adapter' => $this]);
        return true;
    }

    /**
     * Initialize attribute sets code-to-id pairs.
     *
     * @return $this
     */
    protected function _initAttributeSets()
    {
        foreach ($this->_setColFactory->create()->setEntityTypeFilter($this->_entityTypeId) as $attributeSet) {
            $this->_attrSetNameToId[$attributeSet->getAttributeSetName()] = $attributeSet->getId();
            $this->_attrSetIdToName[$attributeSet->getId()] = $attributeSet->getAttributeSetName();
        }
        return $this;
    }

    /**
     * Initialize categories text-path to ID hash.
     *
     * @return $this
     */
    protected function _initCategories()
    {
        $collection = $this->_categoryColFactory->create()->addNameToResult();
        /* @var $collection \Magento\Catalog\Model\Resource\Category\Collection */
        foreach ($collection as $category) {
            $structure = explode('/', $category->getPath());
            $pathSize = count($structure);
            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $collection->getItemById($structure[$i])->getName();
                }
                $rootCategoryName = array_shift($path);
                if (!isset($this->_categoriesWithRoots[$rootCategoryName])) {
                    $this->_categoriesWithRoots[$rootCategoryName] = [];
                }
                $index = implode('/', $path);
                $this->_categoriesWithRoots[$rootCategoryName][$index] = $category->getId();
                if ($pathSize > 2) {
                    $this->_categories[$index] = $category->getId();
                }
            }
        }
        return $this;
    }

    /**
     * Initialize customer groups.
     *
     * @return $this
     */
    protected function _initCustomerGroups()
    {
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $this->_customerGroups[$group->getId()] = true;
        }
        return $this;
    }

    /**
     * Initialize existent product SKUs.
     *
     * @return $this
     */
    protected function _initSkus()
    {
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku'];
        foreach ($this->_productFactory->create()->getProductEntitiesInfo($columns) as $info) {
            $typeId = $info['type_id'];
            $sku = $info['sku'];
            $this->_oldSku[$sku] = [
                'type_id' => $typeId,
                'attr_set_id' => $info['attribute_set_id'],
                'entity_id' => $info['entity_id'],
                'supported_type' => isset($this->_productTypeModels[$typeId]),
            ];
        }
        return $this;
    }

    /**
     * Initialize stores hash.
     *
     * @return $this
     */
    protected function _initStores()
    {
        foreach ($this->_storeManager->getStores() as $store) {
            $this->_storeCodeToId[$store->getCode()] = $store->getId();
            $this->_storeIdToWebsiteStoreIds[$store->getId()] = $store->getWebsite()->getStoreIds();
        }
        return $this;
    }

    /**
     * Initialize product type models.
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initTypeModels()
    {
        $productTypes = $this->_importConfig->getEntityTypes($this->getEntityTypeCode());
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            $params = [$this, $productTypeName];
            if (!($model = $this->_productTypeFactory->create($productTypeConfig['model'], ['params' => $params]))
            ) {
                throw new \Magento\Framework\Model\Exception(
                    sprintf("Entity type model '%s' is not found", $productTypeConfig['model'])
                );
            }
            if (!$model instanceof \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType) {
                throw new \Magento\Framework\Model\Exception(
                    __(
                        'Entity type model must be an instance of ' .
                        'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType'
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
            }
            $this->_specialAttributes = array_merge($this->_specialAttributes, $model->getParticularAttributes());
        }
        // remove doubles
        $this->_specialAttributes = array_unique($this->_specialAttributes);

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
            $this->_websiteCodeToId[$website->getCode()] = $website->getId();
            $this->_websiteCodeToStoreIds[$website->getCode()] = array_flip($website->getStoreCodes());
        }
        return $this;
    }

    /**
     * Check product category validity.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isProductCategoryValid(array $rowData, $rowNum)
    {
        $emptyCategory = empty($rowData[self::COL_CATEGORY]);
        $emptyRootCategory = empty($rowData[self::COL_ROOT_CATEGORY]);
        $hasCategory = $emptyCategory ? false : isset($this->_categories[$rowData[self::COL_CATEGORY]]);
        $category = $emptyRootCategory ? null : $this->_categoriesWithRoots[$rowData[self::COL_ROOT_CATEGORY]];
        if (!$emptyCategory && !$hasCategory || !$emptyRootCategory && !isset(
            $category
        ) || !$emptyRootCategory && !$emptyCategory && !isset(
            $category[$rowData[self::COL_CATEGORY]]
        )
        ) {
            $this->addRowError(self::ERROR_INVALID_CATEGORY, $rowNum);
            return false;
        }
        return true;
    }

    /**
     * Check product website belonging.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isProductWebsiteValid(array $rowData, $rowNum)
    {
        if (!empty($rowData['_product_websites']) && !isset($this->_websiteCodeToId[$rowData['_product_websites']])) {
            $this->addRowError(self::ERROR_INVALID_WEBSITE, $rowNum);
            return false;
        }
        return true;
    }

    /**
     * Set valid attribute set and product type to rows with all scopes
     * to ensure that existing products doesn't changed.
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData = parent::_prepareRowForDb($rowData);

        static $lastSku = null;

        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            return $rowData;
        }
        if (self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
            $lastSku = $rowData[self::COL_SKU];
        }
        if (isset($this->_oldSku[$lastSku])) {
            $rowData[self::COL_ATTR_SET] = $this->_newSku[$lastSku]['attr_set_code'];
            $rowData[self::COL_TYPE] = $this->_newSku[$lastSku]['type_id'];
        }

        return $rowData;
    }

    /**
     * Check tier price data validity.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isTierPriceValid(array $rowData, $rowNum)
    {
        if (isset(
            $rowData['_tier_price_website']
        ) && strlen(
            $rowData['_tier_price_website']
        ) || isset(
            $rowData['_tier_price_customer_group']
        ) && strlen(
            $rowData['_tier_price_customer_group']
        ) || isset(
            $rowData['_tier_price_qty']
        ) && strlen(
            $rowData['_tier_price_qty']
        ) || isset(
            $rowData['_tier_price_price']
        ) && strlen(
            $rowData['_tier_price_price']
        )
        ) {
            if (!isset(
                $rowData['_tier_price_website']
            ) || !isset(
                $rowData['_tier_price_customer_group']
            ) || !isset(
                $rowData['_tier_price_qty']
            ) || !isset(
                $rowData['_tier_price_price']
            ) || !strlen(
                $rowData['_tier_price_website']
            ) || !strlen(
                $rowData['_tier_price_customer_group']
            ) || !strlen(
                $rowData['_tier_price_qty']
            ) || !strlen(
                $rowData['_tier_price_price']
            )
            ) {
                $this->addRowError(self::ERROR_TIER_DATA_INCOMPLETE, $rowNum);
                return false;
            } elseif ($rowData['_tier_price_website'] != self::VALUE_ALL && !isset(
                $this->_websiteCodeToId[$rowData['_tier_price_website']]
            )
            ) {
                $this->addRowError(self::ERROR_INVALID_TIER_PRICE_SITE, $rowNum);
                return false;
            } elseif ($rowData['_tier_price_customer_group'] != self::VALUE_ALL && !isset(
                $this->_customerGroups[$rowData['_tier_price_customer_group']]
            )
            ) {
                $this->addRowError(self::ERROR_INVALID_TIER_PRICE_GROUP, $rowNum);
                return false;
            } elseif ($rowData['_tier_price_qty'] <= 0 || $rowData['_tier_price_price'] <= 0) {
                $this->addRowError(self::ERROR_INVALID_TIER_PRICE_QTY, $rowNum);
                return false;
            }
        }
        return true;
    }

    /**
     * Check group price data validity.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isGroupPriceValid(array $rowData, $rowNum)
    {
        if (isset(
            $rowData['_group_price_website']
        ) && strlen(
            $rowData['_group_price_website']
        ) || isset(
            $rowData['_group_price_customer_group']
        ) && strlen(
            $rowData['_group_price_customer_group']
        ) || isset(
            $rowData['_group_price_price']
        ) && strlen(
            $rowData['_group_price_price']
        )
        ) {
            if (!isset(
                $rowData['_group_price_website']
            ) || !isset(
                $rowData['_group_price_customer_group']
            ) || !strlen(
                $rowData['_group_price_website']
            ) || !strlen(
                $rowData['_group_price_customer_group']
            ) || !strlen(
                $rowData['_group_price_price']
            )
            ) {
                $this->addRowError(self::ERROR_GROUP_PRICE_DATA_INCOMPLETE, $rowNum);
                return false;
            } elseif ($rowData['_group_price_website'] != self::VALUE_ALL && !isset(
                $this->_websiteCodeToId[$rowData['_group_price_website']]
            )
            ) {
                $this->addRowError(self::ERROR_INVALID_GROUP_PRICE_SITE, $rowNum);
                return false;
            } elseif ($rowData['_group_price_customer_group'] != self::VALUE_ALL && !isset(
                $this->_customerGroups[$rowData['_group_price_customer_group']]
            )
            ) {
                $this->addRowError(self::ERROR_INVALID_GROUP_PRICE_GROUP, $rowNum);
                return false;
            }
        }
        return true;
    }

    /**
     * Check media information
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isMediaValid($rowData, $rowNum)
    {
        if (!empty($rowData['_media_image']) && empty($rowData['_media_attribute_id'])) {
            $this->addRowError(self::ERROR_MEDIA_DATA_INCOMPLETE, $rowNum);
            return false;
        }
        return true;
    }

    /**
     * Check super products SKU
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isSuperProductsSkuValid($rowData, $rowNum)
    {
        if (!empty($rowData['_super_products_sku']) && (!isset(
            $this->_oldSku[$rowData['_super_products_sku']]
        ) && !isset(
            $this->_newSku[$rowData['_super_products_sku']]
        ))
        ) {
            $this->addRowError(self::ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND, $rowNum);
            return false;
        }
        return true;
    }

    /**
     * Gather and save information about product links.
     * Must be called after ALL products saving done.
     *
     * @return $this
     */
    protected function _saveLinks()
    {
        $resource = $this->_linkFactory->create();
        $mainTable = $resource->getMainTable();
        $positionAttrId = [];
        $nextLinkId = $this->_resourceHelper->getNextAutoincrement($mainTable);
        $adapter = $this->_connection;

        // pre-load 'position' attributes ID for each link type once
        foreach ($this->_linkNameToId as $linkName => $linkId) {
            $select = $adapter->select()->from(
                $resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $adapter->fetchOne($select, $bind);
        }
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $productIds = [];
            $linkRows = [];
            $positionRows = [];

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                if (self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    $sku = $rowData[self::COL_SKU];
                }
                foreach ($this->_linkNameToId as $linkName => $linkId) {
                    $productId = $this->_newSku[$sku]['entity_id'];
                    $productIds[] = $productId;
                    if (isset($rowData[$linkName . 'sku'])) {
                        $linkedSku = $rowData[$linkName . 'sku'];

                        if ((isset(
                            $this->_newSku[$linkedSku]
                        ) || isset(
                            $this->_oldSku[$linkedSku]
                        )) && $linkedSku != $sku
                        ) {
                            if (isset($this->_newSku[$linkedSku])) {
                                $linkedId = $this->_newSku[$linkedSku]['entity_id'];
                            } else {
                                $linkedId = $this->_oldSku[$linkedSku]['entity_id'];
                            }

                            if ($linkedId == null) {
                                // Import file links to a SKU which is skipped for some reason, which leads to a "NULL"
                                // link causing fatal errors.
                                $this->_logger->critical(
                                    new \Exception(
                                        sprintf(
                                            'WARNING: Orphaned link skipped: From SKU %s (ID %d) to SKU %s, ' .
                                            'Link type id: %d',
                                            $sku,
                                            $productId,
                                            $linkedSku,
                                            $linkId
                                        )
                                    )
                                );
                                continue;
                            }

                            $linkKey = "{$productId}-{$linkedId}-{$linkId}";

                            if (!isset($linkRows[$linkKey])) {
                                $linkRows[$linkKey] = [
                                    'link_id' => $nextLinkId,
                                    'product_id' => $productId,
                                    'linked_product_id' => $linkedId,
                                    'link_type_id' => $linkId,
                                ];
                                if (!empty($rowData[$linkName . 'position'])) {
                                    $positionRows[] = [
                                        'link_id' => $nextLinkId,
                                        'product_link_attribute_id' => $positionAttrId[$linkId],
                                        'value' => $rowData[$linkName . 'position'],
                                    ];
                                }
                                $nextLinkId++;
                            }
                        }
                    }
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior() && $productIds) {
                $adapter->delete($mainTable, $adapter->quoteInto('product_id IN (?)', array_unique($productIds)));
            }
            if ($linkRows) {
                $adapter->insertOnDuplicate($mainTable, $linkRows, ['link_id']);
            }
            if ($positionRows) {
                // process linked product positions
                $adapter->insertOnDuplicate($resource->getAttributeTypeTable('int'), $positionRows, ['value']);
            }
        }
        return $this;
    }

    /**
     * Save product attributes.
     *
     * @param array $attributesData
     * @return $this
     */
    protected function _saveProductAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $skuData) {
            $tableData = [];

            foreach ($skuData as $sku => $attributes) {
                $productId = $this->_newSku[$sku]['entity_id'];

                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        $tableData[] = [
                            'entity_id' => $productId,
                            'entity_type_id' => $this->_entityTypeId,
                            'attribute_id' => $attributeId,
                            'store_id' => $storeId,
                            'value' => $storeValue,
                        ];
                    }
                    /*
                    If the store based values are not provided for a particular store,
                    we default to the default scope values.
                    In this case, remove all the existing store based values stored in the table.
                    */
                    $where = $this->_connection->quoteInto(
                        'store_id NOT IN (?)',
                        array_keys($storeValues)
                    ) . $this->_connection->quoteInto(
                        ' AND attribute_id = ?',
                        $attributeId
                    ) . $this->_connection->quoteInto(
                        ' AND entity_id = ?',
                        $productId
                    ) . $this->_connection->quoteInto(
                        ' AND entity_type_id = ?',
                        $this->_entityTypeId
                    );
                    $this->_connection->delete($tableName, $where);
                }
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }
        return $this;
    }

    /**
     * Save product categories.
     *
     * @param array $categoriesData
     * @return $this
     */
    protected function _saveProductCategories(array $categoriesData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductCategoryTable();
        }
        if ($categoriesData) {
            $categoriesIn = [];
            $delProductId = [];

            foreach ($categoriesData as $delSku => $categories) {
                $productId = $this->_newSku[$delSku]['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($categories) as $categoryId) {
                    $categoriesIn[] = ['product_id' => $productId, 'category_id' => $categoryId, 'position' => 1];
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('product_id IN (?)', $delProductId)
                );
            }
            if ($categoriesIn) {
                $this->_connection->insertOnDuplicate($tableName, $categoriesIn, ['position']);
            }
        }
        return $this;
    }

    /**
     * Update and insert data in entity table.
     *
     * @param array $entityRowsIn Row for insert
     * @param array $entityRowsUp Row for update
     * @return $this
     */
    protected function _saveProductEntity(array $entityRowsIn, array $entityRowsUp)
    {
        static $entityTable = null;

        if (!$entityTable) {
            $entityTable = $this->_resourceFactory->create()->getEntityTable();
        }
        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate($entityTable, $entityRowsUp, ['updated_at']);
        }
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($entityTable, $entityRowsIn);

            $newProducts = $this->_connection->fetchPairs(
                $this->_connection->select()->from(
                    $entityTable,
                    ['sku', 'entity_id']
                )->where(
                    'sku IN (?)',
                    array_keys($entityRowsIn)
                )
            );
            foreach ($newProducts as $sku => $newId) {
                // fill up entity_id for new products
                $this->_newSku[$sku]['entity_id'] = $newId;
            }
        }
        return $this;
    }

    /**
     * Gather and save information about product entities.
     *
     * @return $this
     */
    protected function _saveProducts()
    {
        /** @var $resource \Magento\CatalogImportExport\Model\Import\Proxy\Product\Resource */
        $resource = $this->_resourceFactory->create();
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $websites = [];
            $categories = [];
            $tierPrices = [];
            $groupPrices = [];
            $mediaGallery = [];
            $uploadedGalleryFiles = [];
            $previousType = null;
            $prevAttributeSet = null;

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                if (self::SCOPE_DEFAULT == $rowScope) {
                    $rowSku = $rowData[self::COL_SKU];

                    // 1. Entity phase
                    if (isset($this->_oldSku[$rowSku])) {
                        // existing row
                        $entityRowsUp[] = [
                            'updated_at' => $this->dateTime->now(),
                            'entity_id' => $this->_oldSku[$rowSku]['entity_id'],
                        ];
                    } else {
                        // new row
                        if (!$productLimit || $productsQty < $productLimit) {
                            $entityRowsIn[$rowSku] = [
                                'entity_type_id' => $this->_entityTypeId,
                                'attribute_set_id' => $this->_newSku[$rowSku]['attr_set_id'],
                                'type_id' => $this->_newSku[$rowSku]['type_id'],
                                'sku' => $rowSku,
                                'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                                'created_at' => $this->dateTime->now(),
                                'updated_at' => $this->dateTime->now(),
                            ];
                            $productsQty++;
                        } else {
                            $rowSku = null;
                            // sign for child rows to be skipped
                            $this->_rowsToSkip[$rowNum] = true;
                            continue;
                        }
                    }
                } elseif (null === $rowSku) {
                    $this->_rowsToSkip[$rowNum] = true;
                    // skip rows when SKU is NULL
                    continue;
                } elseif (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE] = $this->_newSku[$rowSku]['type_id'];
                    $rowData['attribute_set_id'] = $this->_newSku[$rowSku]['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->_newSku[$rowSku]['attr_set_code'];
                }

                // 2. Product-to-Website phase
                if (!empty($rowData['_product_websites'])) {
                    $websites[$rowSku][$this->_websiteCodeToId[$rowData['_product_websites']]] = true;
                }

                // 3. Categories phase
                $categoryPath = empty($rowData[self::COL_CATEGORY]) ? '' : $rowData[self::COL_CATEGORY];
                if (!empty($rowData[self::COL_ROOT_CATEGORY])) {
                    $categoryId = $this->_categoriesWithRoots[$rowData[self::COL_ROOT_CATEGORY]][$categoryPath];
                    $categories[$rowSku][$categoryId] = true;
                } elseif (!empty($categoryPath)) {
                    $categories[$rowSku][$this->_categories[$categoryPath]] = true;
                }

                // 4.1. Tier prices phase
                if (!empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty' => $rowData['_tier_price_qty'],
                        'value' => $rowData['_tier_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_tier_price_website'] ||
                        $priceIsGlobal ? 0 : $this->_websiteCodeToId[$rowData['_tier_price_website']],
                    ];
                }

                // 4.2. Group prices phase
                if (!empty($rowData['_group_price_website'])) {
                    $groupPrices[$rowSku][] = [
                        'all_groups' => $rowData['_group_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_group_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_group_price_customer_group'],
                        'value' => $rowData['_group_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_group_price_website'] ||
                        $priceIsGlobal ? 0 : $this->_websiteCodeToId[$rowData['_group_price_website']],
                    ];
                }

                // 5. Media gallery phase
                foreach ($this->_imagesArrayKeys as $imageCol) {
                    if (!empty($rowData[$imageCol])) {
                        if (!array_key_exists($rowData[$imageCol], $uploadedGalleryFiles)) {
                            $uploadedGalleryFiles[$rowData[$imageCol]] = $this->_uploadMediaFiles($rowData[$imageCol]);
                        }
                        $rowData[$imageCol] = $uploadedGalleryFiles[$rowData[$imageCol]];
                    }
                }
                if (!empty($rowData['_media_image'])) {
                    $mediaGallery[$rowSku][] = [
                        'attribute_id' => $rowData['_media_attribute_id'],
                        'label' => $rowData['_media_label'],
                        'position' => $rowData['_media_position'],
                        'disabled' => $rowData['_media_is_disabled'],
                        'value' => $rowData['_media_image'],
                    ];
                }

                // 6. Attributes phase
                $rowStore = self::SCOPE_STORE == $rowScope ? $this->_storeCodeToId[$rowData[self::COL_STORE]] : 0;
                $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                if (!is_null($productType)) {
                    $previousType = $productType;
                }
                if (isset($rowData[self::COL_ATTR_SET])) {
                    $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                }
                if (self::SCOPE_NULL == $rowScope) {
                    // for multiselect attributes only
                    if (!is_null($prevAttributeSet)) {
                        $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                    }
                    if (is_null($productType) && !is_null($previousType)) {
                        $productType = $previousType;
                    }
                    if (is_null($productType)) {
                        continue;
                    }
                }

                if ($this->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND ||
                    empty($rowData[self::COL_SKU])
                ) {
                    $rowData = $this->_productTypeModels[$productType]->clearEmptyData($rowData);
                }

                $rowData = $this->_productTypeModels[$productType]->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    !isset($this->_oldSku[$rowSku])
                );
                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $resource->getAttribute($attrCode);
                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds = [0];

                    if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = new \DateTime('@' . strtotime($attrValue));
                        $attrValue = $attrValue->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->_storeIdToWebsiteStoreIds[$rowStore];
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if ('multiselect' == $attribute->getFrontendInput()) {
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                                $attributes[$attrTable][$rowSku][$attrId][$storeId] = '';
                            } else {
                                $attributes[$attrTable][$rowSku][$attrId][$storeId] .= ',';
                            }
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] .= $attrValue;
                        } else {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
            }

            $this->_saveProductEntity(
                $entityRowsIn,
                $entityRowsUp
            )->_saveProductWebsites(
                $websites
            )->_saveProductCategories(
                $categories
            )->_saveProductTierPrices(
                $tierPrices
            )->_saveProductGroupPrices(
                $groupPrices
            )->_saveMediaGallery(
                $mediaGallery
            )->_saveProductAttributes(
                $attributes
            );
        }
        return $this;
    }

    /**
     * Save product tier prices.
     *
     * @param array $tierPriceData
     * @return $this
     */
    protected function _saveProductTierPrices(array $tierPriceData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getTable('catalog_product_entity_tier_price');
        }
        if ($tierPriceData) {
            $tierPriceIn = [];
            $delProductId = [];

            foreach ($tierPriceData as $delSku => $tierPriceRows) {
                $productId = $this->_newSku[$delSku]['entity_id'];
                $delProductId[] = $productId;

                foreach ($tierPriceRows as $row) {
                    $row['entity_id'] = $productId;
                    $tierPriceIn[] = $row;
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $delProductId)
                );
            }
            if ($tierPriceIn) {
                $this->_connection->insertOnDuplicate($tableName, $tierPriceIn, ['value']);
            }
        }
        return $this;
    }

    /**
     * Save product group prices.
     *
     * @param array $groupPriceData
     * @return $this
     */
    protected function _saveProductGroupPrices(array $groupPriceData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getTable('catalog_product_entity_group_price');
        }
        if ($groupPriceData) {
            $groupPriceIn = [];
            $delProductId = [];

            foreach ($groupPriceData as $delSku => $groupPriceRows) {
                $productId = $this->_newSku[$delSku]['entity_id'];
                $delProductId[] = $productId;

                foreach ($groupPriceRows as $row) {
                    $row['entity_id'] = $productId;
                    $groupPriceIn[] = $row;
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $delProductId)
                );
            }
            if ($groupPriceIn) {
                $this->_connection->insertOnDuplicate($tableName, $groupPriceIn, ['value']);
            }
        }
        return $this;
    }

    /**
     * Returns an object for upload a media files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getUploader()
    {
        if (is_null($this->_fileUploader)) {
            $this->_fileUploader = $this->_uploaderFactory->create();

            $this->_fileUploader->init();

            $tmpPath = $this->_mediaDirectory->getAbsolutePath('import');
            if (!$this->_fileUploader->setTmpDir($tmpPath)) {
                throw new \Magento\Framework\Model\Exception(sprintf("File directory '%s' is not readable.", $tmpPath));
            }
            $destinationDir = "catalog/product";
            $destinationPath = $this->_mediaDirectory->getAbsolutePath($destinationDir);

            $this->_mediaDirectory->create($destinationDir);
            if (!$this->_fileUploader->setDestDir($destinationPath)) {
                throw new \Magento\Framework\Model\Exception(sprintf("File directory '%s' is not writable.", $destinationPath));
            }
        }
        return $this->_fileUploader;
    }

    /**
     * Uploading files into the "catalog/product" media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @return string
     */
    protected function _uploadMediaFiles($fileName)
    {
        try {
            $res = $this->_getUploader()->move($fileName);
            return $res['file'];
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Save product media gallery.
     *
     * @param array $mediaGalleryData
     * @return $this
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        if (empty($mediaGalleryData)) {
            return $this;
        }

        static $mediaGalleryTableName = null;
        static $mediaValueTableName = null;
        static $productId = null;

        if (!$mediaGalleryTableName) {
            $mediaGalleryTableName = $this->_resourceFactory->create()->getTable(
                'catalog_product_entity_media_gallery'
            );
        }

        if (!$mediaValueTableName) {
            $mediaValueTableName = $this->_resourceFactory->create()->getTable(
                'catalog_product_entity_media_gallery_value'
            );
        }

        foreach ($mediaGalleryData as $productSku => $mediaGalleryRows) {
            $productId = $this->_newSku[$productSku]['entity_id'];
            $insertedGalleryImgs = [];

            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $mediaGalleryTableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $productId)
                );
            }

            foreach ($mediaGalleryRows as $insertValue) {
                if (!in_array($insertValue['value'], $insertedGalleryImgs)) {
                    $valueArr = [
                        'attribute_id' => $insertValue['attribute_id'],
                        'entity_id' => $productId,
                        'value' => $insertValue['value'],
                    ];

                    $this->_connection->insertOnDuplicate($mediaGalleryTableName, $valueArr, ['entity_id']);

                    $insertedGalleryImgs[] = $insertValue['value'];
                }

                $newMediaValues = $this->_connection->fetchPairs(
                    $this->_connection->select()->from(
                        $mediaGalleryTableName,
                        ['value', 'value_id']
                    )->where(
                        'entity_id IN (?)',
                        $productId
                    )
                );

                if (array_key_exists($insertValue['value'], $newMediaValues)) {
                    $insertValue['value_id'] = $newMediaValues[$insertValue['value']];
                }

                $valueArr = [
                    'value_id' => $insertValue['value_id'],
                    'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    'entity_id' => $productId,
                    'label' => $insertValue['label'],
                    'position' => $insertValue['position'],
                    'disabled' => $insertValue['disabled'],
                ];

                try {
                    $this->_connection->insertOnDuplicate($mediaValueTableName, $valueArr, ['value_id']);
                } catch (\Exception $e) {
                    $this->_connection->delete(
                        $mediaGalleryTableName,
                        $this->_connection->quoteInto('value_id IN (?)', $newMediaValues)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Save product websites.
     *
     * @param array $websiteData
     * @return $this
     */
    protected function _saveProductWebsites(array $websiteData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductWebsiteTable();
        }
        if ($websiteData) {
            $websitesData = [];
            $delProductId = [];

            foreach ($websiteData as $delSku => $websites) {
                $productId = $this->_newSku[$delSku]['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($websites) as $websiteId) {
                    $websitesData[] = ['product_id' => $productId, 'website_id' => $websiteId];
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('product_id IN (?)', $delProductId)
                );
            }
            if ($websitesData) {
                $this->_connection->insertOnDuplicate($tableName, $websitesData);
            }
        }
        return $this;
    }

    /**
     * Stock item saving.
     *
     * @return $this
     */
    protected function _saveStockItem()
    {
        $indexer = $this->indexerRegistry->get('catalog_product_category');
        /** @var $stockResource \Magento\CatalogInventory\Model\Resource\Stock\Item */
        $stockResource = $this->_stockResItemFac->create();
        $entityTable = $stockResource->getMainTable();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = [];
            $productIdsToReindex = [];
            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                // only SCOPE_DEFAULT can contain stock data
                if (self::SCOPE_DEFAULT != $this->getRowScope($rowData)) {
                    continue;
                }

                $row = [];
                $row['product_id'] = $this->_newSku[$rowData[self::COL_SKU]]['entity_id'];
                $productIdsToReindex[] = $row['product_id'];

                $row['website_id'] = $this->stockConfiguration->getDefaultWebsiteId();
                $row['stock_id'] = $this->stockRegistry->getStock($row['website_id'])->getStockId();

                $stockItemDo = $this->stockRegistry->getStockItem($row['product_id'], $row['website_id']);
                $existStockData = $stockItemDo->getData();

                $row = array_merge(
                    $this->defaultStockData,
                    array_intersect_key($existStockData, $this->defaultStockData),
                    array_intersect_key($rowData, $this->defaultStockData),
                    $row
                );

                if ($this->stockConfiguration->isQty($this->_newSku[$rowData[self::COL_SKU]]['type_id'])) {
                    $row['is_in_stock'] = $this->stockState->verifyStock($row['product_id'], $row['website_id']);
                    if ($this->stockState->verifyNotification($row['product_id'], $row['website_id'])) {
                        $row['low_stock_date'] = $this->_localeDate->date(null, null, null, false)
                            ->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);
                    }
                    $row['stock_status_changed_auto'] = (int) !$this->stockState->verifyStock($row['product_id'], $row['website_id']);
                } else {
                    $row['qty'] = 0;
                }
                $stockData[] = $row;
            }

            // Insert rows
            if (!empty($stockData)) {
                $this->_connection->insertOnDuplicate($entityTable, $stockData);
            }

            if ($productIdsToReindex) {
                $indexer->reindexList($productIdsToReindex);
            }
        }
        return $this;
    }

    /**
     * Attribute set ID-to-name pairs getter.
     *
     * @return array
     */
    public function getAttrSetIdToName()
    {
        return $this->_attrSetIdToName;
    }

    /**
     * DB connection getter.
     *
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'catalog_product';
    }

    /**
     * New products SKU data.
     *
     * @return array
     */
    public function getNewSku()
    {
        return $this->_newSku;
    }

    /**
     * Get next bunch of validated rows.
     *
     * @return array|null
     */
    public function getNextBunch()
    {
        return $this->_dataSourceModel->getNextBunch();
    }

    /**
     * Existing products SKU getter.
     *
     * @return array
     */
    public function getOldSku()
    {
        return $this->_oldSku;
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (!empty($rowData[self::COL_SKU]) && strlen(trim($rowData[self::COL_SKU]))) {
            return self::SCOPE_DEFAULT;
        }
        if (empty($rowData[self::COL_STORE])) {
            return self::SCOPE_NULL;
        }
        return self::SCOPE_STORE;
    }

    /**
     * All website codes to ID getter.
     *
     * @return array
     */
    public function getWebsiteCodes()
    {
        return $this->_websiteCodeToId;
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
        static $sku = null;
        // SKU is remembered through all product rows

        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        if (isset($this->_newSku[$rowData[self::COL_SKU]])) {
            $this->addRowError(self::ERROR_DUPLICATE_SKU, $rowNum);
            return false;
        }
        $rowScope = $this->getRowScope($rowData);

        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                $this->addRowError(self::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }
        // common validation
        $this->_isProductWebsiteValid($rowData, $rowNum);
        $this->_isProductCategoryValid($rowData, $rowNum);
        $this->_isTierPriceValid($rowData, $rowNum);
        $this->_isGroupPriceValid($rowData, $rowNum);
        $this->_isSuperProductsSkuValid($rowData, $rowNum);
        $this->_isMediaValid($rowData, $rowNum);
        $this->isWeightValid($rowData, $rowNum);

        if (self::SCOPE_DEFAULT == $rowScope) {
            // SKU is specified, row is SCOPE_DEFAULT, new product block begins
            $this->_processedEntitiesCount++;

            $sku = $rowData[self::COL_SKU];

            if (isset($this->_oldSku[$sku])) {
                // can we get all necessary data from existent DB product?
                // check for supported type of existing product
                if (isset($this->_productTypeModels[$this->_oldSku[$sku]['type_id']])) {
                    $this->_newSku[$sku] = [
                        'entity_id' => $this->_oldSku[$sku]['entity_id'],
                        'type_id' => $this->_oldSku[$sku]['type_id'],
                        'attr_set_id' => $this->_oldSku[$sku]['attr_set_id'],
                        'attr_set_code' => $this->_attrSetIdToName[$this->_oldSku[$sku]['attr_set_id']],
                    ];
                } else {
                    $this->addRowError(self::ERROR_TYPE_UNSUPPORTED, $rowNum);
                    // child rows of legacy products with unsupported types are orphans
                    $sku = false;
                }
            } else {
                // validate new product type and attribute set
                if (!isset($rowData[self::COL_TYPE]) || !isset($this->_productTypeModels[$rowData[self::COL_TYPE]])) {
                    $this->addRowError(self::ERROR_INVALID_TYPE, $rowNum);
                } elseif (!isset(
                    $rowData[self::COL_ATTR_SET]
                ) || !isset(
                    $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]]
                )
                ) {
                    $this->addRowError(self::ERROR_INVALID_ATTR_SET, $rowNum);
                } elseif (!isset($this->_newSku[$sku])) {
                    $this->_newSku[$sku] = [
                        'entity_id' => null,
                        'type_id' => $rowData[self::COL_TYPE],
                        'attr_set_id' => $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]],
                        'attr_set_code' => $rowData[self::COL_ATTR_SET],
                    ];
                }
                if (isset($this->_invalidRows[$rowNum])) {
                    // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                    $sku = false;
                }
            }
        } else {
            if (null === $sku) {
                $this->addRowError(self::ERROR_SKU_IS_EMPTY, $rowNum);
            } elseif (false === $sku) {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNum);
            } elseif (self::SCOPE_STORE == $rowScope && !isset($this->_storeCodeToId[$rowData[self::COL_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
        }
        if (!isset($this->_invalidRows[$rowNum])) {
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $this->_newSku[$sku]['attr_set_code'];

            $rowAttributesValid = $this->_productTypeModels[$this->_newSku[$sku]['type_id']]->isRowValid(
                $rowData,
                $rowNum,
                !isset($this->_oldSku[$sku])
            );
            if (!$rowAttributesValid && self::SCOPE_DEFAULT == $rowScope) {
                // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                $sku = false;
            }
        }
        // validate custom options
        $this->getOptionEntity()->validateRow($rowData, $rowNum);

        return !isset($this->_invalidRows[$rowNum]);
    }

    /**
     * Validate data rows and save bunches to DB
     *
     * @return $this
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $source->rewind();
        while ($source->valid()) {
            if ($this->_errorsCount >= $this->_errorsLimit) {
                // errors limit check
                return $this;
            }
            $rowData = $source->current();
            $this->validateRow($rowData, $source->key());
            $source->next();
        }
        $this->getOptionEntity()->validateAmbiguousData();
        return parent::_saveValidatedBunches();
    }

    /**
     * Get array of affected products
     *
     * @return int[]
     */
    public function getAffectedEntityIds()
    {
        $productIds = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                if (!isset($this->_newSku[$rowData[self::COL_SKU]]['entity_id'])) {
                    continue;
                }
                $productIds[] = $this->_newSku[$rowData[self::COL_SKU]]['entity_id'];
            }
        }
        return $productIds;
    }

    /**
     * Check weight data
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function isWeightValid($rowData, $rowNum)
    {
        if (!empty($rowData['weight']) && (!is_numeric($rowData['weight']) || $rowData['weight'] < 0)) {
            $this->addRowError(self::ERROR_INVALID_WEIGHT, $rowNum);
            return false;
        }
        return true;
    }
}
