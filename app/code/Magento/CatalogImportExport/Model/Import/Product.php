<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Import entity product model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const CONFIG_KEY_PRODUCT_TYPES = 'global/importexport/import_product_types';

    /**
     * Size of bunch - part of products to save in one step.
     */
    const BUNCH_SIZE = 20;

    /**
     * Size of bunch to delete attributes of products in one step.
     */
    const ATTRIBUTE_DELETE_BUNCH = 1000;

    /**
     * Pseudo multi line separator in one cell.
     *
     * Can be used as custom option value delimiter or in configurable fields cells.
     */
    const PSEUDO_MULTI_LINE_SEPARATOR = '|';

    /**
     * Symbol between Name and Value between Pairs.
     */
    const PAIR_NAME_VALUE_SEPARATOR = '=';

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

    /**
     * Column product store.
     */
    const COL_STORE = '_store';

    /**
     * Column product store view code.
     */
    const COL_STORE_VIEW_CODE = 'store_view_code';

    /**
     * Column website.
     */
    const COL_WEBSITE = 'website_code';

    /**
     * Column product attribute set.
     */
    const COL_ATTR_SET = '_attribute_set';

    /**
     * Column product type.
     */
    const COL_TYPE = 'product_type';

    /**
     * Column product category.
     */
    const COL_CATEGORY = 'categories';

    /**
     * Column product visibility.
     */
    const COL_VISIBILITY = 'visibility';

    /**
     * Column product sku.
     */
    const COL_SKU = 'sku';

    /**
     * Column product name.
     */
    const COL_NAME = 'name';

    /**
     * Column product website.
     */
    const COL_PRODUCT_WEBSITES = '_product_websites';

    /**
     * Media gallery attribute code.
     */
    const MEDIA_GALLERY_ATTRIBUTE_CODE = 'media_gallery';

    /**
     * Column media image.
     */
    const COL_MEDIA_IMAGE = '_media_image';

    /**
     * Inventory use config.
     */
    const INVENTORY_USE_CONFIG = 'Use Config';

    /**
     * Inventory use config prefix.
     */
    const INVENTORY_USE_CONFIG_PREFIX = 'use_config_';

    /**
     * Url key attribute code
     */
    const URL_KEY = 'url_key';

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributeCache = [];

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
     * @var string
     */
    protected $mediaGalleryTableName;

    /**
     * @var string
     */
    protected $mediaGalleryValueTableName;
    /**
     * @var string
     */
    protected $mediaGalleryEntityToValueTableName;

    /**
     * @var string
     */
    protected $productEntityTableName;

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = [
        'status',
        'tax_class_id',
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
     * Attributes codes which shows as date
     *
     * @var array
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
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * Attribute id for product images storage.
     *
     * @var array
     */
    protected $_mediaGalleryAttributeId = null;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_SCOPE => 'Invalid value in Scope column',
        ValidatorInterface::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exist?)',
        ValidatorInterface::ERROR_INVALID_STORE => 'Invalid value in Store column (store doesn\'t exist?)',
        ValidatorInterface::ERROR_INVALID_ATTR_SET => 'Invalid value for Attribute Set column (set doesn\'t exist?)',
        ValidatorInterface::ERROR_INVALID_TYPE => 'Product Type is invalid or not supported',
        ValidatorInterface::ERROR_INVALID_CATEGORY => 'Category does not exist',
        ValidatorInterface::ERROR_VALUE_IS_REQUIRED => 'Please make sure attribute "%s" is not empty.',
        ValidatorInterface::ERROR_TYPE_CHANGED => 'Trying to change type of existing products',
        ValidatorInterface::ERROR_SKU_IS_EMPTY => 'SKU is empty',
        ValidatorInterface::ERROR_NO_DEFAULT_ROW => 'Default values row does not exist',
        ValidatorInterface::ERROR_CHANGE_TYPE => 'Product type change is not allowed',
        ValidatorInterface::ERROR_DUPLICATE_SCOPE => 'Duplicate scope',
        ValidatorInterface::ERROR_DUPLICATE_SKU => 'Duplicate SKU',
        ValidatorInterface::ERROR_CHANGE_ATTR_SET => 'Attribute set change is not allowed',
        ValidatorInterface::ERROR_TYPE_UNSUPPORTED => 'Product type is not supported',
        ValidatorInterface::ERROR_ROW_IS_ORPHAN => 'Orphan rows that will be skipped due default row errors',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group ID is invalid',
        ValidatorInterface::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete',
        ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found',
        ValidatorInterface::ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND => 'Product with specified super products SKU not found',
        ValidatorInterface::ERROR_MEDIA_DATA_INCOMPLETE => 'Media data is incomplete',
        ValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH => 'Attribute %s exceeded max length',
        ValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE => 'Value for \'%s\' attribute contains incorrect value',
        ValidatorInterface::ERROR_ABSENT_REQUIRED_ATTRIBUTE => 'Attribute %s is required',
        ValidatorInterface::ERROR_INVALID_ATTRIBUTE_OPTION => 'Value for \'%s\' attribute contains incorrect value, see acceptable values on settings specified for Admin',
        ValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE => 'Duplicated unique attribute',
        ValidatorInterface::ERROR_INVALID_VARIATIONS_CUSTOM_OPTIONS => 'Value for \'%s\' sub attribute in \'%s\' attribute contains incorrect value, acceptable values are: \'dropdown\', \'checkbox\', \'radio\', \'text\'',
        ValidatorInterface::ERROR_INVALID_MEDIA_URL_OR_PATH => 'Wrong URL/path used for attribute %s',
        ValidatorInterface::ERROR_MEDIA_PATH_NOT_ACCESSIBLE => 'Imported resource (image) does not exist in the local media storage',
        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE => 'Imported resource (image) could not be downloaded from external resource due to timeout or access permissions',
        ValidatorInterface::ERROR_INVALID_WEIGHT => 'Product weight is invalid',
        ValidatorInterface::ERROR_DUPLICATE_URL_KEY => 'Url key: \'%s\' was already generated for an item with the SKU: \'%s\'. You need to specify the unique URL key manually'
    ];

    /**
     * Map between import file fields and system fields/attributes.
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
        '_media_is_disabled' => 'hide_from_product_page',
        Product::COL_STORE => 'store_view_code',
        Product::COL_ATTR_SET => 'attribute_set_code',
        Product::COL_TYPE => 'product_type',
        Product::COL_PRODUCT_WEBSITES => 'product_websites',
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
        '_related_sku' => 'related_skus',
        '_related_position' => 'related_position',
        '_crosssell_sku' => 'crosssell_skus',
        '_crosssell_position' => 'crosssell_position',
        '_upsell_sku' => 'upsell_skus',
        '_upsell_position' => 'upsell_position',
        'meta_keyword' => 'meta_keywords',
    ];

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
        self::COL_STORE,
        self::COL_ATTR_SET,
        self::COL_TYPE,
        self::COL_CATEGORY,
        '_product_websites',
        self::COL_PRODUCT_WEBSITES,
        '_tier_price_website',
        '_tier_price_customer_group',
        '_tier_price_qty',
        '_tier_price_price',
        '_related_sku',
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
        self::COL_MEDIA_IMAGE,
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
    protected $_imagesArrayKeys = ['_media_image', 'image', 'small_image', 'thumbnail', 'swatch_image'];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * Array of supported product types as keys with appropriate model object as value.
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType[]
     */
    protected $_productTypeModels = [];

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
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface
     */
    protected $stockStateProvider;

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
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel
     */
    protected $_resource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\Factory
     */
    protected $_productTypeFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\LinkFactory
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
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory
     */
    protected $_stockResItemFac;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @var Product\SkuProcessor
     */
    protected $skuProcessor;

    /**
     * @var Product\CategoryProcessor
     */
    protected $categoryProcessor;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\Catalog\Model\Product\Url */
    protected $productUrl;

    /** @var array */
    protected $websitesCache = [];

    /** @var array */
    protected $categoriesCache = [];

    /** @var array */
    protected $productUrlSuffix = [];

    /**
     * @var array
     * @deprecated
     */
    protected $productUrlKeys = [];

    /**
     * Instance of product tax class processor.
     *
     * @var Product\TaxClassProcessor
     */
    protected $taxClassProcessor;

    /**
     * @var Product\Validator
     */
    protected $validator;

    /**
     * Array of validated rows.
     *
     * @var array
     */
    protected $validatedRows;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * {@inheritdoc}
     */
    protected $masterAttributeCode = 'sku';

    /**
     * @var ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * @var TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * Flag for replace operation.
     *
     * @var null
     */
    protected $_replaceFlag = null;

    /**
     * Flag for replace operation.
     *
     * @var null
     */
    protected $cachedImages = null;

    /** @var array */
    protected $urlKeys = [];

    /** @var array */
    protected $rowNumbers = [];

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Product entity identifier field
     *
     * @var string
     */
    private $productEntityIdentifierField;

    /**
     * Escaped separator value for regular expression.
     * The value is based on PSEUDO_MULTI_LINE_SEPARATOR constant.
     * @var string
     */
    private $multiLineSeparatorForRegexp;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ImportExport\Model\Import\Config $importConfig
     * @param Proxy\Product\ResourceFactory $resourceFactory
     * @param Product\OptionFactory $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param Product\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory
     * @param Proxy\ProductFactory $proxyProdFactory
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac
     * @param DateTime\TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param Product\StoreResolver $storeResolver
     * @param Product\SkuProcessor $skuProcessor
     * @param Product\CategoryProcessor $categoryProcessor
     * @param Product\Validator $validator
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param Product\TaxClassProcessor $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     * @param array $dateAttrCodes
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        Product\StoreResolver $storeResolver,
        Product\SkuProcessor $skuProcessor,
        Product\CategoryProcessor $categoryProcessor,
        Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        Product\TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        array $data = [],
        array $dateAttrCodes = []
    ) {
        $this->_eventManager = $eventManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStateProvider = $stockStateProvider;
        $this->_catalogData = $catalogData;
        $this->_importConfig = $importConfig;
        $this->_resourceFactory = $resourceFactory;
        $this->_setColFactory = $setColFactory;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_linkFactory = $linkFactory;
        $this->_proxyProdFactory = $proxyProdFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_stockResItemFac = $stockResItemFac;
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->indexerRegistry = $indexerRegistry;
        $this->_logger = $logger;
        $this->storeResolver = $storeResolver;
        $this->skuProcessor = $skuProcessor;
        $this->categoryProcessor = $categoryProcessor;
        $this->validator = $validator;
        $this->objectRelationProcessor = $objectRelationProcessor;
        $this->transactionManager = $transactionManager;
        $this->taxClassProcessor = $taxClassProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->productUrl = $productUrl;
        $this->dateAttrCodes = array_merge($this->dateAttrCodes, $dateAttrCodes);
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator
        );
        $this->_optionEntity = isset(
            $data['option_entity']
        ) ? $data['option_entity'] : $optionFactory->create(
            ['data' => ['product_entity' => $this]]
        );

        $this->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus();
        $this->validator->init($this);
    }

    /**
     * Check one attribute. Can be overridden in child.
     *
     * @param string $attrCode Attribute code
     * @param array $attrParams Attribute params
     * @param array $rowData Row data
     * @param int $rowNum
     * @return bool
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        if (!$this->validator->isAttributeValid($attrCode, $attrParams, $rowData)) {
            foreach ($this->validator->getMessages() as $message) {
                $this->addRowError($message, $rowNum, $attrCode);
            }
            return false;
        }
        return true;
    }

    /**
     *
     * Multiple value separator getter.
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
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
     * Retrieve id of media gallery attribute.
     *
     * @return int
     */
    public function getMediaGalleryAttributeId()
    {
        if (!$this->_mediaGalleryAttributeId) {
            /** @var $resource \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel */
            $resource = $this->_resourceFactory->create();
            $this->_mediaGalleryAttributeId = $resource->getAttribute(self::MEDIA_GALLERY_ATTRIBUTE_CODE)->getId();
        }
        return $this->_mediaGalleryAttributeId;
    }

    /**
     * @param string $name
     * @return Product\Type\AbstractType
     */
    public function retrieveProductTypeByName($name)
    {
        if (isset($this->_productTypeModels[$name])) {
            return $this->_productTypeModels[$name];
        }
        return null;
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
     * Delete products for replacement.
     *
     * @return $this
     */
    public function deleteProductsForReplacement()
    {
        $this->setParameters(array_merge(
            $this->getParameters(),
            ['behavior' => Import::BEHAVIOR_DELETE]
        ));
        $this->_deleteProducts();

        return $this;
    }

    /**
     * Delete products.
     *
     * @return $this
     * @throws \Exception
     */
    protected function _deleteProducts()
    {
        $productEntityTable = $this->_resourceFactory->create()->getEntityTable();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $idsToDelete = [];

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    $idsToDelete[] = $this->_oldSku[$rowData[self::COL_SKU]]['entity_id'];
                }
            }
            if ($idsToDelete) {
                $this->countItemsDeleted += count($idsToDelete);
                $this->transactionManager->start($this->_connection);
                try {
                    $this->objectRelationProcessor->delete(
                        $this->transactionManager,
                        $this->_connection,
                        $productEntityTable,
                        $this->_connection->quoteInto('entity_id IN (?)', $idsToDelete),
                        ['entity_id' => $idsToDelete]
                    );
                    $this->_eventManager->dispatch(
                        'catalog_product_import_bunch_delete_commit_before',
                        [
                            'adapter' => $this,
                            'bunch' => $bunch,
                            'ids_to_delete' => $idsToDelete,
                        ]
                    );
                    $this->transactionManager->commit();
                } catch (\Exception $e) {
                    $this->transactionManager->rollBack();
                    throw $e;
                }
                $this->_eventManager->dispatch('catalog_product_import_bunch_delete_after', ['adapter' => $this, 'bunch' => $bunch]);
            }
        }
        return $this;
    }

    /**
     * Create Product entity from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _importData()
    {
        $this->_validatedRows = null;
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteProducts();
        } elseif (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->_replaceFlag = true;
            $this->_replaceProducts();
        } else {
            $this->_saveProductsData();
        }
        $this->_eventManager->dispatch('catalog_product_import_finish_before', ['adapter' => $this]);
        return true;
    }

    /**
     * Replace imported products.
     *
     * @return $this
     */
    protected function _replaceProducts()
    {
        $this->deleteProductsForReplacement();
        $this->_oldSku = $this->skuProcessor->reloadOldSkus()->getOldSkus();
        $this->_validatedRows = null;
        $this->setParameters(array_merge(
            $this->getParameters(),
            ['behavior' => Import::BEHAVIOR_APPEND]
        ));
        $this->_saveProductsData();

        return $this;
    }

    /**
     * Save products data.
     *
     * @return $this
     */
    protected function _saveProductsData()
    {
        $this->_saveProducts();
        foreach ($this->_productTypeModels as $productTypeModel) {
            $productTypeModel->saveData();
        }
        $this->_saveLinks();
        $this->_saveStockItem();
        if ($this->_replaceFlag) {
            $this->getOptionEntity()->clearProductsSkuToId();
        }
        $this->getOptionEntity()->importData();

        return $this;
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
     * Initialize existent product SKUs.
     *
     * @return $this
     */
    protected function _initSkus()
    {
        $this->skuProcessor->setTypeModels($this->_productTypeModels);
        $this->_oldSku = $this->skuProcessor->getOldSkus();
        return $this;
    }

    /**
     * Initialize product type models.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initTypeModels()
    {
        $productTypes = $this->_importConfig->getEntityTypes($this->getEntityTypeCode());
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            $params = [$this, $productTypeName];
            if (!($model = $this->_productTypeFactory->create($productTypeConfig['model'], ['params' => $params]))
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Entity type model \'%1\' is not found', $productTypeConfig['model'])
                );
            }
            if (!$model instanceof \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Entity type model must be an instance of '
                        . \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
            }
            $this->_fieldsMap = array_merge($this->_fieldsMap, $model->getCustomFieldsMapping());
            $this->_specialAttributes = array_merge($this->_specialAttributes, $model->getParticularAttributes());
        }
        $this->_initErrorTemplates();
        // remove doubles
        $this->_specialAttributes = array_unique($this->_specialAttributes);

        return $this;
    }

    /**
     * Initialize Product error templates
     */
    protected function _initErrorTemplates()
    {
        foreach ($this->_messageTemplates as $errorCode => $template) {
            $this->addMessageTemplate($errorCode, $template);
        }
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
        $rowData = $this->_customFieldsMapping($rowData);

        $rowData = parent::_prepareRowForDb($rowData);

        static $lastSku = null;

        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            return $rowData;
        }

        $lastSku = $rowData[self::COL_SKU];

        if (isset($this->_oldSku[$lastSku])) {
            $newSku = $this->skuProcessor->getNewSku($lastSku);
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];
            $rowData[self::COL_TYPE] = $newSku['type_id'];
        }

        return $rowData;
    }

    /**
     * Gather and save information about product links.
     * Must be called after ALL products saving done.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _saveLinks()
    {
        $resource = $this->_linkFactory->create();
        $mainTable = $resource->getMainTable();
        $positionAttrId = [];
        $nextLinkId = $this->_resourceHelper->getNextAutoincrement($mainTable);

        // pre-load 'position' attributes ID for each link type once
        foreach ($this->_linkNameToId as $linkName => $linkId) {
            $select = $this->_connection->select()->from(
                $resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $this->_connection->fetchOne($select, $bind);
        }
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $productIds = [];
            $linkRows = [];
            $positionRows = [];

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $sku = $rowData[self::COL_SKU];

                $productId = $this->skuProcessor->getNewSku($sku)[$this->getProductEntityLinkField()];
                $productLinkKeys = [];
                $select = $this->_connection->select()->from(
                    $resource->getTable('catalog_product_link'),
                    ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
                )->where(
                    'product_id = :product_id'
                );
                $bind = [':product_id' => $productId];
                foreach ($this->_connection->fetchAll($select, $bind) as $linkData) {
                    $linkKey = "{$productId}-{$linkData['linked_id']}-{$linkData['link_type_id']}";
                    $productLinkKeys[$linkKey] = $linkData['id'];
                }
                foreach ($this->_linkNameToId as $linkName => $linkId) {
                    $productIds[] = $productId;
                    if (isset($rowData[$linkName . 'sku'])) {
                        $linkSkus = explode($this->getMultipleValueSeparator(), $rowData[$linkName . 'sku']);
                        $linkPositions = !empty($rowData[$linkName . 'position'])
                            ? explode($this->getMultipleValueSeparator(), $rowData[$linkName . 'position'])
                            : [];
                        foreach ($linkSkus as $linkedKey => $linkedSku) {
                            $linkedSku = trim($linkedSku);
                            if ((!is_null(
                                        $this->skuProcessor->getNewSku($linkedSku)
                                    ) || isset(
                                        $this->_oldSku[$linkedSku]
                                    )) && $linkedSku != $sku
                            ) {
                                $newSku = $this->skuProcessor->getNewSku($linkedSku);
                                if (!empty($newSku)) {
                                    $linkedId = $newSku['entity_id'];
                                } else {
                                    $linkedId = $this->_oldSku[$linkedSku]['entity_id'];
                                }

                                if ($linkedId == null) {
                                    // Import file links to a SKU which is skipped for some reason,
                                    // which leads to a "NULL"
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
                                if (empty($productLinkKeys[$linkKey])) {
                                    $productLinkKeys[$linkKey] = $nextLinkId;
                                }
                                if (!isset($linkRows[$linkKey])) {
                                    $linkRows[$linkKey] = [
                                        'link_id' => $productLinkKeys[$linkKey],
                                        'product_id' => $productId,
                                        'linked_product_id' => $linkedId,
                                        'link_type_id' => $linkId,
                                    ];
                                    if (!empty($linkPositions[$linkedKey])) {
                                        $positionRows[] = [
                                            'link_id' => $productLinkKeys[$linkKey],
                                            'product_link_attribute_id' => $positionAttrId[$linkId],
                                            'value' => $linkPositions[$linkedKey],
                                        ];
                                    }
                                    $nextLinkId++;
                                }
                            }
                        }
                    }
                }
            }
            if (Import::BEHAVIOR_APPEND != $this->getBehavior() && $productIds) {
                $this->_connection->delete(
                    $mainTable,
                    $this->_connection->quoteInto('product_id IN (?)', array_unique($productIds))
                );
            }
            if ($linkRows) {
                $this->_connection->insertOnDuplicate($mainTable, $linkRows, ['link_id']);
            }
            if ($positionRows) {
                // process linked product positions
                $this->_connection->insertOnDuplicate(
                    $resource->getAttributeTypeTable('int'),
                    $positionRows, ['value']
                );
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
                $linkId = $this->_connection->fetchOne(
                    $this->_connection->select()
                        ->from($this->getResource()->getTable('catalog_product_entity'))
                        ->where('sku = ?', (string) $sku)
                        ->columns($this->getProductEntityLinkField())
                );

                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        $tableData[] = [
                            $this->getProductEntityLinkField() => $linkId,
                            'attribute_id' => $attributeId,
                            'store_id' => $storeId,
                            'value' => $storeValue,
                        ];
                    }
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
                $productId = $this->skuProcessor->getNewSku($delSku)['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($categories) as $categoryId) {
                    $categoriesIn[] = ['product_id' => $productId, 'category_id' => $categoryId, 'position' => 1];
                }
            }
            if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('product_id IN (?)', $delProductId)
                );
            }
            if ($categoriesIn) {
                $this->_connection->insertOnDuplicate($tableName, $categoriesIn, ['product_id', 'category_id']);
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
    public function saveProductEntity(array $entityRowsIn, array $entityRowsUp)
    {
        static $entityTable = null;
        $this->countItemsCreated += count($entityRowsIn);
        $this->countItemsUpdated += count($entityRowsUp);

        if (!$entityTable) {
            $entityTable = $this->_resourceFactory->create()->getEntityTable();
        }
        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate($entityTable, $entityRowsUp, ['updated_at']);
        }
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($entityTable, $entityRowsIn);

            $select = $this->_connection->select()->from(
                $entityTable,
                array_merge($this->getNewSkuFieldsForSelect(), $this->getOldSkuFieldsForSelect())
            )->where(
                'sku IN (?)',
                array_keys($entityRowsIn)
            );
            $newProducts = $this->_connection->fetchAll($select);
            foreach ($newProducts as $data) {
                $sku = $data['sku'];
                unset($data['sku']);
                foreach ($data as $key => $value) {
                    $this->skuProcessor->setNewSkuData($sku, $key, $value);
                }
            }

            $this->updateOldSku($newProducts);
        }

        return $this;
    }

    /**
     * Return additional data, needed to select.
     * @return array
     */
    private function getOldSkuFieldsForSelect()
    {
        return ['type_id', 'attribute_set_id'];
    }

    /**
     * Adds newly created products to _oldSku
     * @param array $newProducts
     * @return void
     */
    private function updateOldSku(array $newProducts)
    {
        $oldSkus = [];
        foreach ($newProducts as $info) {
            $typeId = $info['type_id'];
            $sku = $info['sku'];
            $oldSkus[$sku] = [
                'type_id' => $typeId,
                'attr_set_id' => $info['attribute_set_id'],
                $this->getProductIdentifierField() => $info[$this->getProductIdentifierField()],
                'supported_type' => isset($this->_productTypeModels[$typeId]),
                $this->getProductEntityLinkField() => $info[$this->getProductEntityLinkField()],
            ];
        }

        $this->_oldSku = array_replace($this->_oldSku, $oldSkus);
    }

    /**
     * Get new SKU fields for select
     *
     * @return array
     */
    private function getNewSkuFieldsForSelect()
    {
        $fields = ['sku', $this->getProductEntityLinkField()];
        if ($this->getProductEntityLinkField() != $this->getProductIdentifierField()) {
            $fields[] = $this->getProductIdentifierField();
        }
        return $fields;
    }

    /**
     * Init media gallery resources
     * @return void
     */
    protected function initMediaGalleryResources()
    {
        if (null == $this->mediaGalleryTableName) {
            $this->productEntityTableName = $this->getResource()->getTable('catalog_product_entity');
            $this->mediaGalleryTableName = $this->getResource()->getTable('catalog_product_entity_media_gallery');
            $this->mediaGalleryValueTableName = $this->getResource()->getTable(
                'catalog_product_entity_media_gallery_value'
            );
            $this->mediaGalleryEntityToValueTableName = $this->getResource()->getTable(
                'catalog_product_entity_media_gallery_value_to_entity'
            );
        }
    }

    /**
     * Get existing images for current bunch
     *
     * @param array $bunch
     * @return array
     */
    protected function getExistingImages($bunch)
    {
        $result = [];
        if ($this->getErrorAggregator()->hasToBeTerminated()) {
            return $result;
        }

        $this->initMediaGalleryResources();
        $productSKUs = array_map('strval', array_column($bunch, self::COL_SKU));
        $select = $this->_connection->select()->from(
            ['mg' => $this->mediaGalleryTableName],
            ['value' => 'mg.value']
        )->joinInner(
            ['mgvte' => $this->mediaGalleryEntityToValueTableName],
            '(mg.value_id = mgvte.value_id)',
            [
                $this->getProductEntityLinkField() => 'mgvte.' . $this->getProductEntityLinkField(),
                'value_id' => 'mgvte.value_id'
            ]
        )->joinLeft(
            ['mgv' => $this->mediaGalleryValueTableName],
            sprintf(
                '(mg.value_id = mgv.value_id AND mgv.%s = mgvte.%s AND mgv.store_id = %d)',
                $this->getProductEntityLinkField(),
                $this->getProductEntityLinkField(),
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            ),
            [
                'label' => 'mgv.label'
            ]
        )->joinInner(
            ['pe' => $this->productEntityTableName],
            "(mgvte.{$this->getProductEntityLinkField()} = pe.{$this->getProductEntityLinkField()})",
            ['sku' => 'pe.sku']
        )->where(
            'pe.sku IN (?)',
            $productSKUs
        );

        foreach ($this->_connection->fetchAll($select) as $image) {
            $result[$image['sku']][$image['value']] = $image;
        }

        return $result;
    }

    /**
     * @param array $rowData
     * @return array
     */
    public function getImagesFromRow(array $rowData)
    {
        $images = [];
        $labels = [];
        foreach ($this->_imagesArrayKeys as $column) {
            if (!empty($rowData[$column])) {
                $images[$column] = array_unique(
                    array_map(
                        'trim',
                        explode($this->getMultipleValueSeparator(), $rowData[$column])
                    )
                );

                if (!empty($rowData[$column . '_label'])) {
                    $labels[$column] = $this->parseMultipleValues($rowData[$column . '_label']);

                    if (count($labels[$column]) > count($images[$column])) {
                        $labels[$column] = array_slice($labels[$column], 0, count($images[$column]));
                    }
                }
            }
        }

        return [$images, $labels];
    }

    /**
     * Gather and save information about product entities.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _saveProducts()
    {
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $this->websitesCache = [];
            $this->categoriesCache = [];
            $tierPrices = [];
            $mediaGallery = [];
            $labelsForUpdate = [];
            $uploadedImages = [];
            $previousType = null;
            $prevAttributeSet = null;
            $existingImages = $this->getExistingImages($bunch);

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                if (empty($rowData[self::URL_KEY])) {
                    $rowData[self::URL_KEY] = $this->getUrlKey($rowData);
                }

                $rowSku = $rowData[self::COL_SKU];

                if (null === $rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                } elseif (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                    $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                }

                // 1. Entity phase
                if (isset($this->_oldSku[$rowSku])) {
                    // existing row
                    $entityRowsUp[] = [
                        'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        $this->getProductEntityLinkField() => $this->_oldSku[$rowSku][$this->getProductEntityLinkField()],
                    ];
                } else {
                    if (!$productLimit || $productsQty < $productLimit) {
                        $entityRowsIn[$rowSku] = [
                            'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                            'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                            'sku' => $rowSku,
                            'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                            'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                            'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        ];
                        $productsQty++;
                    } else {
                        $rowSku = null;
                        // sign for child rows to be skipped
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                }

                if (!array_key_exists($rowSku, $this->websitesCache)) {
                    $this->websitesCache[$rowSku] = [];
                }
                // 2. Product-to-Website phase
                if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                    $websiteCodes = explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]);
                    foreach ($websiteCodes as $websiteCode) {
                        $websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCode);
                        $this->websitesCache[$rowSku][$websiteId] = true;
                    }
                }

                // 3. Categories phase
                if (!array_key_exists($rowSku, $this->categoriesCache)) {
                    $this->categoriesCache[$rowSku] = [];
                }
                $rowData['rowNum'] = $rowNum;
                $categoryIds = $this->processRowCategories($rowData);
                foreach ($categoryIds as $id) {
                    $this->categoriesCache[$rowSku][$id] = true;
                }
                unset($rowData['rowNum']);

                // 4.1. Tier prices phase
                if (!empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty' => $rowData['_tier_price_qty'],
                        'value' => $rowData['_tier_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_tier_price_website'] ||
                        $priceIsGlobal ? 0 : $this->storeResolver->getWebsiteCodeToId($rowData['_tier_price_website']),
                    ];
                }

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                // 5. Media gallery phase
                $disabledImages = [];
                list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
                if (isset($rowData['_media_is_disabled'])) {
                    $disabledImages = array_flip(
                        explode($this->getMultipleValueSeparator(), $rowData['_media_is_disabled'])
                    );
                }
                $rowData[self::COL_MEDIA_IMAGE] = [];
                foreach ($rowImages as $column => $columnImages) {
                    foreach ($columnImages as $position => $columnImage) {
                        if (!isset($uploadedImages[$columnImage])) {
                            $uploadedFile = $this->uploadMediaFiles($columnImage, true);
                            if ($uploadedFile) {
                                $uploadedImages[$columnImage] = $uploadedFile;
                            } else {
                                $this->addRowError(
                                    ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                    $rowNum,
                                    null,
                                    null,
                                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                );
                            }
                        } else {
                            $uploadedFile = $uploadedImages[$columnImage];
                        }

                        if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                            $rowData[$column] = $uploadedFile;
                        }

                        if ($uploadedFile && !isset($mediaGallery[$rowSku][$uploadedFile])) {
                            if (isset($existingImages[$rowSku][$uploadedFile])) {
                                if (isset($rowLabels[$column][$position])
                                    && $rowLabels[$column][$position] != $existingImages[$rowSku][$uploadedFile]['label']
                                ) {
                                    $labelsForUpdate[] = [
                                        'label' => $rowLabels[$column][$position],
                                        'imageData' => $existingImages[$rowSku][$uploadedFile]
                                    ];
                                }
                            } else {
                                if ($column == self::COL_MEDIA_IMAGE) {
                                    $rowData[$column][] = $uploadedFile;
                                }
                                $mediaGallery[$rowSku][$uploadedFile] = [
                                    'attribute_id' => $this->getMediaGalleryAttributeId(),
                                    'label' => isset($rowLabels[$column][$position]) ? $rowLabels[$column][$position] : '',
                                    'position' => $position + 1,
                                    'disabled' => isset($disabledImages[$columnImage]) ? '1' : '0',
                                    'value' => $uploadedFile,
                                ];
                            }
                        }
                    }
                }

                // 6. Attributes phase
                $rowStore = (self::SCOPE_STORE == $rowScope)
                    ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                    : 0;
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

                $productTypeModel = $this->_productTypeModels[$productType];
                if (!empty($rowData['tax_class_name'])) {
                    $rowData['tax_class_id'] =
                        $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                }

                if ($this->getBehavior() == Import::BEHAVIOR_APPEND ||
                    empty($rowData[self::COL_SKU])
                ) {
                    $rowData = $productTypeModel->clearEmptyData($rowData);
                }

                $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    !isset($this->_oldSku[$rowSku])
                );
                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $this->retrieveAttributeByCode($attrCode);

                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds = [0];

                    if (
                        'datetime' == $attribute->getBackendType()
                        && (
                            in_array($attribute->getAttributeCode(), $this->dateAttrCodes)
                            || $attribute->getIsUserDefined()
                        )
                    ) {
                        $attrValue = $this->dateTime->formatDate($attrValue, false);
                    } else if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            $this->_localeDate->date($attrValue)->getTimestamp()
                        );
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                        if (!isset($this->_oldSku[$rowSku])) {
                            $storeIds[] = 0;
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
            }

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    unset($bunch[$rowNum]);
                }
            }

            $this->saveProductEntity(
                $entityRowsIn,
                $entityRowsUp
            )->_saveProductWebsites(
                $this->websitesCache
            )->_saveProductCategories(
                $this->categoriesCache
            )->_saveProductTierPrices(
                $tierPrices
            )->_saveMediaGallery(
                $mediaGallery
            )->_saveProductAttributes(
                $attributes
            )->updateMediaGalleryLabels(
                $labelsForUpdate
            );

            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $bunch]
            );
        }

        return $this;
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function processRowCategories($rowData)
    {
        $categoriesString = empty($rowData[self::COL_CATEGORY]) ? '' : $rowData[self::COL_CATEGORY];
        $categoryIds = [];
        if (!empty($categoriesString)) {
            $categoryIds = $this->categoryProcessor->upsertCategories(
                $categoriesString,
                $this->getMultipleValueSeparator()
            );
            foreach ($this->categoryProcessor->getFailedCategories() as $error) {
                $this->errorAggregator->addError(
                    AbstractEntity::ERROR_CODE_CATEGORY_NOT_VALID,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
                    $rowData['rowNum'],
                    self::COL_CATEGORY,
                    __('Category "%1" has not been created.', $error['category'])
                    . ' ' . $error['exception']->getMessage()
                );
            }
        }
        return $categoryIds;
    }

    /**
     * @param string $productSku
     * @return array
     */
    public function getProductWebsites($productSku)
    {
        return array_keys($this->websitesCache[$productSku]);
    }

    /**
     * @param string $productSku
     * @return array
     */
    public function getProductCategories($productSku)
    {
        return array_keys($this->categoriesCache[$productSku]);
    }

    /**
     * @param string $storeCode
     * @return array|int|null|string
     */
    public function getStoreIdByCode($storeCode)
    {
        if (empty($storeCode)) {
            return self::SCOPE_DEFAULT;
        }
        return $this->storeResolver->getStoreCodeToId($storeCode);
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
                $productId = $this->skuProcessor->getNewSku($delSku)[$this->getProductEntityLinkField()];
                $delProductId[] = $productId;

                foreach ($tierPriceRows as $row) {
                    $row[$this->getProductEntityLinkField()] = $productId;
                    $tierPriceIn[] = $row;
                }
            }
            if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto("{$this->getProductEntityLinkField()} IN (?)", $delProductId)
                );
            }
            if ($tierPriceIn) {
                $this->_connection->insertOnDuplicate($tableName, $tierPriceIn, ['value']);
            }
        }
        return $this;
    }

    /**
     * Returns an object for upload a media files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploader()
    {
        if (is_null($this->_fileUploader)) {
            $this->_fileUploader = $this->_uploaderFactory->create();

            $this->_fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

            $DS = DIRECTORY_SEPARATOR;

            if (!empty($this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR])) {
                $tmpPath = $this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR];
            } else {
                $tmpPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath('import');
            }

            if (!$this->_fileUploader->setTmpDir($tmpPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = "catalog/product";
            $destinationPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath($destinationDir);

            $this->_mediaDirectory->create($destinationPath);
            if (!$this->_fileUploader->setDestDir($destinationPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }
        return $this->_fileUploader;
    }

    /**
     * @return Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUploader()
    {
        return $this->_getUploader();
    }

    /**
     * Uploading files into the "catalog/product" media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        try {
            $res = $this->_getUploader()->move($fileName, $renameFileOff);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        if (empty($mediaGalleryData)) {
            return $this;
        }
        $this->initMediaGalleryResources();
        $productIds = [];
        $imageNames = [];
        $multiInsertData = [];
        $valueToProductId = [];
        foreach ($mediaGalleryData as $productSku => $mediaGalleryRows) {
            $productId = $this->skuProcessor->getNewSku($productSku)[$this->getProductEntityLinkField()];
            $productIds[] = $productId;
            $insertedGalleryImgs = [];
            foreach ($mediaGalleryRows as $insertValue) {
                if (!in_array($insertValue['value'], $insertedGalleryImgs)) {
                    $valueArr = [
                        'attribute_id' => $insertValue['attribute_id'],
                        'value' => $insertValue['value'],
                    ];
                    $valueToProductId[$insertValue['value']][] = $productId;
                    $imageNames[] = $insertValue['value'];
                    $multiInsertData[] = $valueArr;
                    $insertedGalleryImgs[] = $insertValue['value'];
                }
            }
        }
        $oldMediaValues = $this->_connection->fetchAssoc(
            $this->_connection->select()->from($this->mediaGalleryTableName, ['value_id', 'value'])
                ->where('value IN (?)', $imageNames)
        );
        $this->_connection->insertOnDuplicate($this->mediaGalleryTableName, $multiInsertData, []);
        $multiInsertData = [];
        $newMediaSelect = $this->_connection->select()->from($this->mediaGalleryTableName, ['value_id', 'value'])
            ->where('value IN (?)', $imageNames);
        if (array_keys($oldMediaValues)) {
            $newMediaSelect->where('value_id NOT IN (?)', array_keys($oldMediaValues));
        }

        $dataForSkinnyTable = [];
        $newMediaValues = $this->_connection->fetchAssoc($newMediaSelect);
        foreach ($mediaGalleryData as $productSku => $mediaGalleryRows) {
            foreach ($mediaGalleryRows as $insertValue) {
                foreach ($newMediaValues as $value_id => $values) {
                    if ($values['value'] == $insertValue['value']) {
                        $insertValue['value_id'] = $value_id;
                        $insertValue[$this->getProductEntityLinkField()]
                            = array_shift($valueToProductId[$values['value']]);
                        unset($newMediaValues[$value_id]);
                        break;
                    }
                }
                if (isset($insertValue['value_id'])) {
                    $valueArr = [
                        'value_id' => $insertValue['value_id'],
                        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        $this->getProductEntityLinkField() => $insertValue[$this->getProductEntityLinkField()],
                        'label' => $insertValue['label'],
                        'position' => $insertValue['position'],
                        'disabled' => $insertValue['disabled'],
                    ];
                    $multiInsertData[] = $valueArr;
                    $dataForSkinnyTable[] = [
                        'value_id' => $insertValue['value_id'],
                        $this->getProductEntityLinkField() => $insertValue[$this->getProductEntityLinkField()],
                    ];
                }
            }
        }
        try {
            $this->_connection->insertOnDuplicate(
                $this->mediaGalleryValueTableName,
                $multiInsertData,
                ['value_id', 'store_id', $this->getProductEntityLinkField(), 'label', 'position', 'disabled']
            );
            $this->_connection->insertOnDuplicate(
                $this->mediaGalleryEntityToValueTableName,
                $dataForSkinnyTable,
                ['value_id']
            );
        } catch (\Exception $e) {
            $this->_connection->delete(
                $this->mediaGalleryTableName,
                $this->_connection->quoteInto('value_id IN (?)', $newMediaValues)
            );
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
                $productId = $this->skuProcessor->getNewSku($delSku)['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($websites) as $websiteId) {
                    $websitesData[] = ['product_id' => $productId, 'website_id' => $websiteId];
                }
            }
            if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
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
        /** @var $stockResource \Magento\CatalogInventory\Model\ResourceModel\Stock\Item */
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

                $row = [];
                $row['product_id'] = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
                $productIdsToReindex[] = $row['product_id'];

                $row['website_id'] = $this->stockConfiguration->getDefaultScopeId();
                $row['stock_id'] = $this->stockRegistry->getStock($row['website_id'])->getStockId();

                $stockItemDo = $this->stockRegistry->getStockItem($row['product_id'], $row['website_id']);
                $existStockData = $stockItemDo->getData();

                $row = array_merge(
                    $this->defaultStockData,
                    array_intersect_key($existStockData, $this->defaultStockData),
                    array_intersect_key($rowData, $this->defaultStockData),
                    $row
                );

                if ($this->stockConfiguration->isQty(
                    $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['type_id']
                )) {
                    $stockItemDo->setData($row);
                    $row['is_in_stock'] = $this->stockStateProvider->verifyStock($stockItemDo);
                    if ($this->stockStateProvider->verifyNotification($stockItemDo)) {
                        $row['low_stock_date'] = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            (new \DateTime())->getTimestamp()
                        );
                    }
                    $row['stock_status_changed_auto'] =
                        (int) !$this->stockStateProvider->verifyStock($stockItemDo);
                } else {
                    $row['qty'] = 0;
                }
                if (!isset($stockData[$rowData[self::COL_SKU]])) {
                    $stockData[$rowData[self::COL_SKU]] = $row;
                }
            }

            // Insert rows
            if (!empty($stockData)) {
                $this->_connection->insertOnDuplicate($entityTable, array_values($stockData));
            }

            if ($productIdsToReindex) {
                $indexer->reindexList($productIdsToReindex);
            }
        }
        return $this;
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $attrCode
     * @return mixed
     */
    public function retrieveAttributeByCode($attrCode)
    {
        if (!isset($this->_attributeCache[$attrCode])) {
            $this->_attributeCache[$attrCode] = $this->getResource()->getAttribute($attrCode);
        }
        return $this->_attributeCache[$attrCode];
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
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
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
     * @var string $sku
     * @return array
     */
    public function getNewSku($sku = null)
    {
        return $this->skuProcessor->getNewSku($sku);
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
     * Retrieve Category Processor
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
     */
    public function getCategoryProcessor()
    {
        return $this->categoryProcessor;
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (empty($rowData[self::COL_STORE])) {
            return self::SCOPE_DEFAULT;
        }
        return self::SCOPE_STORE;
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        $rowScope = $this->getRowScope($rowData);

        // BEHAVIOR_DELETE and BEHAVIOR_REPLACE use specific validation logic
        if (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
        }
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }

        if (!$this->validator->isValid($rowData)) {
            foreach ($this->validator->getMessages() as $message) {
                $this->addRowError($message, $rowNum, $this->validator->getInvalidAttribute());
            }
        }

        $sku = $rowData[self::COL_SKU];
        if (null === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
        } elseif (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        } elseif (self::SCOPE_STORE == $rowScope
            && !$this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
        ) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_STORE, $rowNum);
        }

        // SKU is specified, row is SCOPE_DEFAULT, new product block begins
        $this->_processedEntitiesCount++;

        $sku = $rowData[self::COL_SKU];

        if (isset($this->_oldSku[$sku]) && Import::BEHAVIOR_REPLACE !== $this->getBehavior()) {
            // can we get all necessary data from existent DB product?
            // check for supported type of existing product
            if (isset($this->_productTypeModels[$this->_oldSku[$sku]['type_id']])) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    $this->prepareNewSkuData($sku)
                );
            } else {
                $this->addRowError(ValidatorInterface::ERROR_TYPE_UNSUPPORTED, $rowNum);
                // child rows of legacy products with unsupported types are orphans
                $sku = false;
            }
        } else {
            // validate new product type and attribute set
            if (!isset($rowData[self::COL_TYPE]) || !isset($this->_productTypeModels[$rowData[self::COL_TYPE]])) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID_TYPE, $rowNum);
            } elseif (!isset(
                    $rowData[self::COL_ATTR_SET]
                ) || !isset(
                    $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]]
                )
            ) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID_ATTR_SET, $rowNum);
            } elseif (is_null($this->skuProcessor->getNewSku($sku))) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    [
                        'row_id' => null,
                        'entity_id' => null,
                        'type_id' => $rowData[self::COL_TYPE],
                        'attr_set_id' => $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]],
                        'attr_set_code' => $rowData[self::COL_ATTR_SET],
                    ]
                );
            }
            if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                $sku = false;
            }
        }

        if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
            $newSku = $this->skuProcessor->getNewSku($sku);
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];

            /** @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType $productTypeValidator */
            $productTypeValidator = $this->_productTypeModels[$newSku['type_id']];
            $rowAttributesValid = $productTypeValidator->isRowValid(
                $rowData,
                $rowNum,
                !(isset($this->_oldSku[$sku]) && Import::BEHAVIOR_REPLACE !== $this->getBehavior())
            );
            if (!$rowAttributesValid && self::SCOPE_DEFAULT == $rowScope) {
                // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                $sku = false;
            }
        }
        // validate custom options
        $this->getOptionEntity()->validateRow($rowData, $rowNum);

        if ($this->isNeedToValidateUrlKey($rowData)) {
            $urlKey = $this->getUrlKey($rowData);
            $storeCodes = empty($rowData[self::COL_STORE_VIEW_CODE])
                ? array_flip($this->storeResolver->getStoreCodeToId())
                : explode($this->getMultipleValueSeparator(), $rowData[self::COL_STORE_VIEW_CODE]);
            foreach ($storeCodes as $storeCode) {
                $storeId = $this->storeResolver->getStoreCodeToId($storeCode);
                $productUrlSuffix = $this->getProductUrlSuffix($storeId);
                $urlPath = $urlKey . $productUrlSuffix;
                if (empty($this->urlKeys[$storeId][$urlPath])
                    || ($this->urlKeys[$storeId][$urlPath] == $rowData[self::COL_SKU])
                ) {
                    $this->urlKeys[$storeId][$urlPath] = $rowData[self::COL_SKU];
                    $this->rowNumbers[$storeId][$urlPath] = $rowNum;
                } else {
                    $message = sprintf(
                        $this->retrieveMessageTemplate(ValidatorInterface::ERROR_DUPLICATE_URL_KEY),
                        $urlKey,
                        $this->urlKeys[$storeId][$urlPath]
                    );
                    $this->addRowError(
                        ValidatorInterface::ERROR_DUPLICATE_URL_KEY,
                        $rowNum,
                        $rowData[self::COL_NAME],
                        $message
                    );
                }
            }
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param array $rowData
     * @return bool
     */
    private function isNeedToValidateUrlKey($rowData)
    {
        return (!empty($rowData[self::URL_KEY]) || !empty($rowData[self::COL_NAME]))
            && (empty($rowData[self::COL_VISIBILITY])
            || $rowData[self::COL_VISIBILITY]
            !== (string)Visibility::getOptionArray()[Visibility::VISIBILITY_NOT_VISIBLE]);
    }

    /**
     * Prepare new SKU data
     *
     * @param string $sku
     * @return array
     */
    private function prepareNewSkuData($sku)
    {
        $data = [];
        foreach ($this->_oldSku[$sku] as $key => $value) {
            $data[$key] = $value;
        }

        $data['attr_set_code'] = $this->_attrSetIdToName[$this->_oldSku[$sku]['attr_set_id']];

        return $data;
    }

    /**
     * Parse attributes names and values string to array.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _parseAdditionalAttributes($rowData)
    {
        if (empty($rowData['additional_attributes'])) {
            return $rowData;
        }
        $rowData = array_merge($rowData, $this->parseAdditionalAttributes($rowData['additional_attributes']));
        return $rowData;
    }

    /**
     * Retrieves additional attributes in format:
     * [
     *      code1 => value1,
     *      code2 => value2,
     *      ...
     *      codeN => valueN
     * ]
     *
     * @param string $additionalAttributes Attributes data that will be parsed
     * @return array
     */
    private function parseAdditionalAttributes($additionalAttributes)
    {
        return empty($this->_parameters[Import::FIELDS_ENCLOSURE])
            ? $this->parseAttributesWithoutWrappedValues($additionalAttributes)
            : $this->parseAttributesWithWrappedValues($additionalAttributes);
    }

    /**
     * Parses data and returns attributes in format:
     * [
     *      code1 => value1,
     *      code2 => value2,
     *      ...
     *      codeN => valueN
     * ]
     *
     * @param string $attributesData Attributes data that will be parsed. It keeps data in format:
     *      code=value,code2=value2...,codeN=valueN
     * @return array
     */
    private function parseAttributesWithoutWrappedValues($attributesData)
    {
        $attributeNameValuePairs = explode($this->getMultipleValueSeparator(), $attributesData);
        $preparedAttributes = [];
        $code = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if (strpos($attributeData, self::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (!$code) {
                    continue;
                }
                $preparedAttributes[$code] .= $this->getMultipleValueSeparator() . $attributeData;
                continue;
            }
            list($code, $value) = explode(self::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
            $preparedAttributes[$code] = $value;
        }
        return $preparedAttributes;
    }

    /**
     * Parses data and returns attributes in format:
     * [
     *      code1 => value1,
     *      code2 => value2,
     *      ...
     *      codeN => valueN
     * ]
     * All values have unescaped data except mupliselect attributes,
     * they should be parsed in additional method - parseMultiselectValues()
     *
     * @param string $attributesData Attributes data that will be parsed. It keeps data in format:
     *      code="value",code2="value2"...,codeN="valueN"
     *  where every value is wrapped in double quotes. Double quotes as part of value should be duplicated.
     *  E.g. attribute with code 'attr_code' has value 'my"value'. This data should be stored as attr_code="my""value"
     *
     * @return array
     */
    private function parseAttributesWithWrappedValues($attributesData)
    {
        $attributes = [];
        preg_match_all('~((?:[a-z0-9_])+)="((?:[^"]|""|"' . $this->getMultiLineSeparatorForRegexp() . '")+)"+~',
            $attributesData,
            $matches
        );
        foreach ($matches[1] as $i => $attributeCode) {
            $attribute = $this->retrieveAttributeByCode($attributeCode);
            $value = 'multiselect' != $attribute->getFrontendInput()
                ? str_replace('""', '"', $matches[2][$i])
                : '"' . $matches[2][$i] . '"';
            $attributes[$attributeCode] = $value;
        }
        return $attributes;
    }

    /**
     * Parse values of multiselect attributes depends on "Fields Enclosure" parameter
     *
     * @param string $values
     * @param string $delimiter
     * @return array
     */
    public function parseMultiselectValues($values, $delimiter = self::PSEUDO_MULTI_LINE_SEPARATOR)
    {
        if (empty($this->_parameters[Import::FIELDS_ENCLOSURE])) {
            return explode($delimiter, $values);
        }
        if (preg_match_all('~"((?:[^"]|"")*)"~', $values, $matches)) {
            return $values = array_map(function ($value) {
                return str_replace('""', '"', $value);
            }, $matches[1]);
        }
        return [$values];
    }

    /**
     * Retrieves escaped PSEUDO_MULTI_LINE_SEPARATOR if it is metacharacter for regular expression
     *
     * @return string
     */
    private function getMultiLineSeparatorForRegexp()
    {
        if (!$this->multiLineSeparatorForRegexp) {
            $this->multiLineSeparatorForRegexp = in_array(self::PSEUDO_MULTI_LINE_SEPARATOR, str_split('[\^$.|?*+(){}'))
                ? '\\' . self::PSEUDO_MULTI_LINE_SEPARATOR
                : self::PSEUDO_MULTI_LINE_SEPARATOR;
        }
        return $this->multiLineSeparatorForRegexp;
    }

    /**
     * Set values in use_config_ fields.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _setStockUseConfigFieldsValues($rowData)
    {
        $useConfigFields = [];
        foreach ($rowData as $key => $value) {
            $useConfigName = self::INVENTORY_USE_CONFIG_PREFIX . $key;
            if (isset($this->defaultStockData[$key])
                && isset($this->defaultStockData[$useConfigName])
                && !empty($value)
                && empty($rowData[$useConfigName])
            ) {
                $useConfigFields[$useConfigName] = ($value == self::INVENTORY_USE_CONFIG) ? 1 : 0;
            }
        }
        $rowData = array_merge($rowData, $useConfigFields);
        return $rowData;
    }

    /**
     * Custom fields mapping for changed purposes of fields and field names.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _customFieldsMapping($rowData)
    {
        foreach ($this->_fieldsMap as $systemFieldName => $fileFieldName) {
            if (array_key_exists($fileFieldName, $rowData)) {
                $rowData[$systemFieldName] = $rowData[$fileFieldName];
            }
        }

        $rowData = $this->_parseAdditionalAttributes($rowData);

        $rowData = $this->_setStockUseConfigFieldsValues($rowData);
        if (array_key_exists('status', $rowData)
            && $rowData['status'] != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        ) {
            if ($rowData['status'] == 'yes') {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            } elseif (!empty($rowData['status']) || $this->getRowScope($rowData) == self::SCOPE_DEFAULT) {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            }
        }
        return $rowData;
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
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                $this->_processedRowsCount++;
                $source->next();
                continue;
            }

            $rowData = $this->_customFieldsMapping($rowData);

            $this->validateRow($rowData, $source->key());

            $source->next();
        }
        $this->checkUrlKeyDuplicates();
        $this->getOptionEntity()->validateAmbiguousData();
        return parent::_saveValidatedBunches();
    }

    /**
     * Check that url_keys are not assigned to other products in DB
     *
     * @return void
     */
    protected function checkUrlKeyDuplicates()
    {
        $resource = $this->getResource();
        foreach ($this->urlKeys as $storeId => $urlKeys) {
            $urlKeyDuplicates = $this->_connection->fetchAssoc(
                $this->_connection->select()->from(
                    ['url_rewrite' => $resource->getTable('url_rewrite')],
                    ['request_path', 'store_id']
                )->joinLeft(
                    ['cpe' => $resource->getTable('catalog_product_entity')],
                    "cpe.entity_id = url_rewrite.entity_id"
                )->where('request_path IN (?)', array_keys($urlKeys))
                    ->where('store_id IN (?)', $storeId)
                    ->where('cpe.sku not in (?)', array_values($urlKeys))
            );
            foreach ($urlKeyDuplicates as $entityData) {
                $rowNum = $this->rowNumbers[$entityData['store_id']][$entityData['request_path']];
                $this->addRowError(ValidatorInterface::ERROR_DUPLICATE_URL_KEY, $rowNum);
            }
        }
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    protected function getProductUrlSuffix($storeId = null)
    {
        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productUrlSuffix[$storeId];
    }

    /**
     * @param array $rowData
     * @return string
     */
    protected function getUrlKey($rowData)
    {
        if (!empty($rowData[self::URL_KEY])) {
            return $rowData[self::URL_KEY];
        }

        if (!empty($rowData[self::COL_NAME])) {
            return $this->productUrl->formatUrlKey($rowData[self::COL_NAME]);
        }

        return '';
    }

    /**
     * @return Proxy\Product\ResourceModel
     */
    protected function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = $this->_resourceFactory->create();
        }
        return $this->_resource;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     */
    private function getProductIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }

    /**
     * Update media gallery labels
     *
     * @param array $labels
     * @return void
     */
    private function updateMediaGalleryLabels(array $labels)
    {
        if (empty($labels)) {
            return;
        }

        $insertData = [];
        foreach ($labels as $label) {
            $imageData = $label['imageData'];

            if ($imageData['label'] === null) {
                $insertData[] = [
                    'label' => $label['label'],
                    $this->getProductEntityLinkField() => $imageData[$this->getProductEntityLinkField()],
                    'value_id' => $imageData['value_id'],
                    'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ];
            } else {
                $this->_connection->update(
                    $this->mediaGalleryValueTableName,
                    [
                        'label' => $label['label']
                    ],
                    [
                        $this->getProductEntityLinkField() . ' = ?' => $imageData[$this->getProductEntityLinkField()],
                        'value_id = ?' => $imageData['value_id'],
                        'store_id = ?' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                );
            }
        }

        if (!empty($insertData)) {
            $this->_connection->insertMultiple(
                $this->mediaGalleryValueTableName,
                $insertData
            );
        }
    }

    /**
     * Parse values from multiple attributes fields
     *
     * @param string $labelRow
     * @return array
     */
    private function parseMultipleValues($labelRow)
    {
        return $this->parseMultiselectValues(
            $labelRow,
            $this->getMultipleValueSeparator()
        );
    }
}
