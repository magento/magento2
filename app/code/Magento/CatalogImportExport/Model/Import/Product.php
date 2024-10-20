<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Model\Import;

use Magento\AwsS3\Driver\AwsS3;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceIndexer;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;
use Magento\CatalogImportExport\Model\Import\Product\LinkProcessor;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Skip;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogImportExport\Model\Import\Product\StatusProcessor;
use Magento\CatalogImportExport\Model\Import\Product\StockProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\CatalogImportExport\Model\StockItemProcessorInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Store\Model\Store;

/**
 * Import entity product model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class Product extends AbstractEntity
{
    private const COL_NAME_FORMAT = '/[\x00-\x1F\x7F]/';
    public const CONFIG_KEY_PRODUCT_TYPES = 'global/importexport/import_product_types';

    /**
     * Filter chain const
     */
    private const FILTER_CHAIN = "php://filter";

    /**
     * Size of bunch - part of products to save in one step.
     */
    public const BUNCH_SIZE = 20;

    /**
     * Size of bunch to delete attributes of products in one step.
     */
    public const ATTRIBUTE_DELETE_BUNCH = 1000;

    /**
     * Pseudo multi line separator in one cell.
     *
     * Can be used as custom option value delimiter or in configurable fields cells.
     */
    public const PSEUDO_MULTI_LINE_SEPARATOR = '|';

    /**
     * Symbol between Name and Value between Pairs.
     */
    public const PAIR_NAME_VALUE_SEPARATOR = '=';

    /**
     * Value that means all entities (e.g. websites, groups etc.)
     */
    public const VALUE_ALL = 'all';

    /**
     * Data row scopes.
     */
    public const SCOPE_DEFAULT = 1;

    public const SCOPE_WEBSITE = 2;

    public const SCOPE_STORE = 0;

    public const SCOPE_NULL = -1;

    /**
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */

    /**
     * Column product store.
     */
    public const COL_STORE = '_store';

    /**
     * Column product store view code.
     */
    public const COL_STORE_VIEW_CODE = 'store_view_code';

    /**
     * Column website.
     */
    public const COL_WEBSITE = 'website_code';

    /**
     * Column product attribute set.
     */
    public const COL_ATTR_SET = '_attribute_set';

    /**
     * Column product type.
     */
    public const COL_TYPE = 'product_type';

    /**
     * Column product category.
     */
    public const COL_CATEGORY = 'categories';

    /**
     * Column product visibility.
     */
    public const COL_VISIBILITY = 'visibility';

    /**
     * Column product sku.
     */
    public const COL_SKU = 'sku';

    /**
     * Column product name.
     */
    public const COL_NAME = 'name';

    /**
     * Column product website.
     */
    public const COL_PRODUCT_WEBSITES = '_product_websites';

    /**
     * Attribute code for media gallery.
     */
    public const MEDIA_GALLERY_ATTRIBUTE_CODE = 'media_gallery';

    /**
     * Column media image.
     */
    public const COL_MEDIA_IMAGE = '_media_image';

    /**
     * Inventory use config label.
     */
    public const INVENTORY_USE_CONFIG = 'Use Config';

    /**
     * Prefix for inventory use config.
     */
    public const INVENTORY_USE_CONFIG_PREFIX = 'use_config_';

    /**
     * Url key attribute code
     */
    public const URL_KEY = 'url_key';

    /**
     * Hide from product page attribute code
     */
    public const COL_MEDIA_IMAGE_HIDE = '_media_is_disabled';

    /**
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
     * @since 100.0.4
     */
    protected $mediaGalleryTableName;

    /**
     * @var string
     * @since 100.0.4
     */
    protected $mediaGalleryValueTableName;
    /**
     * @var string
     * @since 100.0.4
     */
    protected $mediaGalleryEntityToValueTableName;

    /**
     * @var string
     * @since 100.0.4
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
     * @deprecated 101.1.0 use DI for LinkProcessor class if you want to add additional types
     *
     * @see Magento_CatalogImportExport::etc/di.xml
     * @var array
     */
    protected $_linkNameToId = [
        '_related_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
        '_crosssell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
        '_upsell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
    ];

    /**
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
     * @var string
     */
    private $hashAlgorithm = 'crc32c';

    /**
     * @var array
     * @codingStandardsIgnoreStart
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
        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE => 'Imported resource (image: %s) at row %s could not be downloaded from external resource due to timeout or access permissions',
        ValidatorInterface::ERROR_INVALID_WEIGHT => 'Product weight is invalid',
        ValidatorInterface::ERROR_DUPLICATE_URL_KEY => 'Url key: \'%s\' was already generated for an item with the SKU: \'%s\'. You need to specify the unique URL key manually',
        ValidatorInterface::ERROR_DUPLICATE_MULTISELECT_VALUES => 'Value for multiselect attribute %s contains duplicated values',
        'invalidNewToDateValue' => 'Make sure new_to_date is later than or the same as new_from_date',
        // Can't add new translated strings in patch release
        'invalidLayoutUpdate' => 'Invalid format.',
        'insufficientPermissions' => 'Invalid format.',
        ValidatorInterface::ERROR_SKU_MARGINAL_WHITESPACES => 'SKU contains marginal whitespaces'
    ];
    //@codingStandardsIgnoreEnd

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
        self::COL_MEDIA_IMAGE_HIDE => 'hide_from_product_page',
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
        self::COL_MEDIA_IMAGE_HIDE
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
     * Note: the order of array items has a value in order to properly set 'position' value
     * of media gallery items.
     *
     * @var string[]
     */
    protected $_imagesArrayKeys = [];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * Array of supported product types as keys with appropriate model object as value.
     *
     * @var AbstractType[]
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
     * @deprecated 101.0.0 this variable isn't used anymore.
     * @see we don't recommend this approach anymore
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

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 100.0.3
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     * @since 100.0.3
     */
    protected $productUrl;

    /**
     * @var array
     */
    protected $websitesCache = [];

    /**
     * @var array
     */
    protected $categoriesCache = [];

    /**
     * @var array
     * @since 100.0.3
     */
    protected $productUrlSuffix = [];

    /**
     * @var array
     * @deprecated 100.0.3
     *
     * @since 100.0.3
     * @see we don't recommend this approach anymore
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
     * @var string
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

    /**
     * @var array
     * @since 100.0.3
     */
    protected $urlKeys = [];

    /**
     * @var array
     * @since 100.0.3
     */
    protected $rowNumbers = [];

    /**
     * @var string
     */
    private $productEntityLinkField;

    /**
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
     * Container for filesystem object.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var StockItemImporterInterface
     */
    private $stockItemImporter;

    /**
     * @var ImageTypeProcessor
     */
    private $imageTypeProcessor;

    /**
     * Provide ability to process and save images during import.
     *
     * @var MediaGalleryProcessor
     */
    private $mediaProcessor;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StatusProcessor
     */
    private $statusProcessor;
    /**
     * @var StockProcessor
     */
    private $stockProcessor;

    /**
     * @var LinkProcessor
     */
    private $linkProcessor;

    /**
     * @var StockItemProcessorInterface
     */
    private $stockItemProcessor;

    /**
     * @var SkuStorage|null
     */
    private ?SkuStorage $skuStorage;

    /**
     * @var File|null
     */
    private ?File $fileDriver;

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
     * @param Proxy\Product\ResourceModelFactory $resourceFactory
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
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param array $data
     * @param array $dateAttrCodes
     * @param CatalogConfig $catalogConfig
     * @param ImageTypeProcessor $imageTypeProcessor
     * @param MediaGalleryProcessor $mediaProcessor
     * @param StockItemImporterInterface|null $stockItemImporter
     * @param DateTimeFactory $dateTimeFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param StatusProcessor|null $statusProcessor
     * @param StockProcessor|null $stockProcessor
     * @param LinkProcessor|null $linkProcessor
     * @param File|null $fileDriver
     * @param StockItemProcessorInterface|null $stockItemProcessor
     * @param SkuStorage|null $skuStorage
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        array $dateAttrCodes = [],
        CatalogConfig $catalogConfig = null,
        ImageTypeProcessor $imageTypeProcessor = null,
        MediaGalleryProcessor $mediaProcessor = null,
        StockItemImporterInterface $stockItemImporter = null,
        DateTimeFactory $dateTimeFactory = null,
        ProductRepositoryInterface $productRepository = null,
        StatusProcessor $statusProcessor = null,
        StockProcessor $stockProcessor = null,
        LinkProcessor $linkProcessor = null,
        ?File $fileDriver = null,
        ?StockItemProcessorInterface $stockItemProcessor = null,
        ?SkuStorage $skuStorage = null
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
        $this->filesystem = $filesystem;
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
        $this->catalogConfig = $catalogConfig ?: ObjectManager::getInstance()->get(CatalogConfig::class);
        $this->imageTypeProcessor = $imageTypeProcessor ?: ObjectManager::getInstance()->get(ImageTypeProcessor::class);
        $this->mediaProcessor = $mediaProcessor ?: ObjectManager::getInstance()->get(MediaGalleryProcessor::class);
        $this->stockItemImporter = $stockItemImporter ?: ObjectManager::getInstance()
            ->get(StockItemImporterInterface::class);
        $this->statusProcessor = $statusProcessor ?: ObjectManager::getInstance()
            ->get(StatusProcessor::class);
        $this->stockProcessor = $stockProcessor ?: ObjectManager::getInstance()
            ->get(StockProcessor::class);
        $this->linkProcessor = $linkProcessor ?? ObjectManager::getInstance()
                ->get(LinkProcessor::class);
        $this->linkProcessor->addNameToIds($this->_linkNameToId);
        $this->hashAlgorithm = (version_compare(PHP_VERSION, '8.1.0') >= 0) ? 'xxh128' : 'crc32c';
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
        $this->_optionEntity = $data['option_entity'] ??
            $optionFactory->create(['data' => ['product_entity' => $this]]);
        $this->skuStorage = $skuStorage ?? ObjectManager::getInstance()
                ->get(SkuStorage::class);
        $this->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus()
            ->initImagesArrayKeys();
        $this->validator->init($this);
        $this->dateTimeFactory = $dateTimeFactory ?? ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->productRepository = $productRepository ?? ObjectManager::getInstance()
                ->get(ProductRepositoryInterface::class);
        $this->stockItemProcessor = $stockItemProcessor ?? ObjectManager::getInstance()
                ->get(StockItemProcessorInterface::class);
        $this->fileDriver = $fileDriver ?? ObjectManager::getInstance()
            ->get(File::class);
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
                $this->skipRow($rowNum, $message, ProcessingError::ERROR_LEVEL_NOT_CRITICAL, $attrCode);
            }
            return false;
        }
        return true;
    }

    /**
     * Multiple value separator getter.
     *
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
     * Return empty attribute value constant
     *
     * @return string
     * @since 101.0.0
     */
    public function getEmptyAttributeValueConstant()
    {
        if (!empty($this->_parameters[Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT])) {
            return $this->_parameters[Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT];
        }
        return Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT;
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
     * Retrieve product type by name.
     *
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
        $this->setParameters(
            array_merge(
                $this->getParameters(),
                ['behavior' => Import::BEHAVIOR_DELETE]
            )
        );
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

        while ($bunch = $this->_dataSourceModel->getNextUniqueBunch($this->getIds())) {
            $idsToDelete = [];

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    $idsToDelete[] = $this->getExistingSku($rowData[self::COL_SKU])['entity_id'];
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
                $this->_eventManager->dispatch(
                    'catalog_product_import_bunch_delete_after',
                    [
                        'adapter' => $this,
                        'bunch' => $bunch,
                        'ids_to_delete' => $idsToDelete,
                    ]
                );
                $this->reindexProducts($idsToDelete);
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
        $this->skuStorage->reset();
        $this->_validatedRows = null;
        $this->setParameters(
            array_merge(
                $this->getParameters(),
                ['behavior' => Import::BEHAVIOR_APPEND]
            )
        );
        $this->_saveProductsData();

        return $this;
    }

    /**
     * Save products data.
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _saveProductsData()
    {
        $this->_saveProducts();
        foreach ($this->_productTypeModels as $productTypeModel) {
            $productTypeModel->saveData();
        }
        $this->linkProcessor->saveLinks(
            $this,
            $this->_dataSourceModel,
            $this->getProductEntityLinkField(),
            $this->getIds()
        );
        $this->_saveStockItem();
        if ($this->_replaceFlag) {
            $this->getOptionEntity()->clearProductsSkuToId();
        }
        $this->getOptionEntity()->setIds($this->getIds());
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
        $this->skuStorage->reset();
        return $this;
    }

    /**
     * Initialize image array keys.
     *
     * @return $this
     */
    private function initImagesArrayKeys()
    {
        $this->_imagesArrayKeys = $this->imageTypeProcessor->getImageTypes();
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
        // When multiple imports are processed in a single php process,
        // these memory caches may interfere with the import result.
        AbstractType::$commonAttributesCache = [];
        AbstractType::$invAttributesCache = [];
        AbstractType::$attributeCodeToId = [];
        $productTypes = $this->_importConfig->getEntityTypes($this->getEntityTypeCode());
        $fieldsMap = [];
        $specialAttributes = [];
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            $params = [$this, $productTypeName];
            if (!($model = $this->_productTypeFactory->create($productTypeConfig['model'], ['params' => $params]))
            ) {
                throw new LocalizedException(
                    __('Entity type model \'%1\' is not found', $productTypeConfig['model'])
                );
            }
            if (!$model instanceof AbstractType) {
                throw new LocalizedException(
                    __(
                        'Entity type model must be an instance of '
                        . AbstractType::class
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
            }
            $fieldsMap[] = $model->getCustomFieldsMapping();
            $specialAttributes[] = $model->getParticularAttributes();
        }
        $this->_fieldsMap = array_merge([], $this->_fieldsMap, ...$fieldsMap);
        $this->_initErrorTemplates();
        // remove doubles
        $this->_specialAttributes = array_unique(array_merge([], $this->_specialAttributes, ...$specialAttributes));

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
     * Set valid attribute set and product type to rows.
     *
     * Set valid attribute set and product type to rows with all
     * scopes to ensure that existing products doesn't changed.
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

        if ($this->isSkuExist($lastSku)) {
            $newSku = $this->skuProcessor->getNewSku($lastSku);
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];
            $rowData[self::COL_TYPE] = $newSku['type_id'];
        }

        return $rowData;
    }

    /**
     * Gather and save information about product links.
     *
     * Must be called after ALL products saving done.
     *
     * @deprecated 101.1.0 use linkProcessor Directly
     * @see linkProcessor
     *
     * @return $this
     */
    protected function _saveLinks()
    {
        $this->linkProcessor->saveLinks($this, $this->_dataSourceModel, $this->getProductEntityLinkField(), []);
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
        $linkField = $this->getProductEntityLinkField();
        $statusAttributeId = (int) $this->retrieveAttributeByCode('status')->getId();
        foreach ($attributesData as $tableName => $skuData) {
            $linkIdBySkuForStatusChanged = [];
            $tableData = [];
            foreach ($skuData as $sku => $attributes) {
                $linkId = $this->skuStorage->get((string)$sku)[$linkField];
                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        if ($attributeId === $statusAttributeId) {
                            $this->statusProcessor->setStatus($sku, $storeId, $storeValue);
                            $linkIdBySkuForStatusChanged[strtolower($sku)] = $linkId;
                        }
                        $tableData[] = [
                            $linkField => $linkId,
                            'attribute_id' => $attributeId,
                            'store_id' => $storeId,
                            'value' => $storeValue,
                        ];
                    }
                }
            }
            if ($linkIdBySkuForStatusChanged) {
                $this->statusProcessor->loadOldStatus($linkIdBySkuForStatusChanged);
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }

        return $this;
    }

    /**
     * Get data for updating product-category relations
     *
     * @param array $categoriesData
     * @param string $tableName
     * @return array
     */
    private function getProductCategoriesDataSave(array $categoriesData, string $tableName): array
    {
        $delProductId = [];
        $categoriesIn = [];
        $minCategoryPosition = [];
        foreach ($categoriesData as $delSku => $categories) {
            $productId = $this->skuProcessor->getNewSku($delSku)['entity_id'];
            $delProductId[] = $productId;

            foreach (array_keys($categories) as $categoryId) {
                //position new products before existing ones
                if (!isset($minCategoryPosition[$categoryId])) {
                    $select = $this->_connection->select()
                        ->from($tableName, ['position' => new \Zend_Db_Expr('MIN(position)')])
                        ->where('category_id = ?', $categoryId);
                    $minCategoryPosition[$categoryId] = (int)$this->_connection->fetchOne($select);
                }
                $categoriesIn[] = [
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                    'position' => --$minCategoryPosition[$categoryId]
                ];
            }
        }
        return [$delProductId, $categoriesIn];
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
            list($delProductId, $categoriesIn) = $this->getProductCategoriesDataSave($categoriesData, $tableName);

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
     * @since 100.1.0
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
            $this->_connection->insertOnDuplicate($entityTable, $entityRowsUp, ['updated_at', 'attribute_set_id']);
        }
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($entityTable, $entityRowsIn);
            $select = $this->_connection->select()->from(
                $entityTable,
                array_merge($this->getNewSkuFieldsForSelect(), $this->getOldSkuFieldsForSelect())
            )->where(
                $this->_connection->quoteInto('sku IN (?)', array_keys($entityRowsIn))
            );
            $newProducts = $this->_connection->fetchAll($select);
            foreach ($newProducts as $data) {
                $sku = $data['sku'];
                unset($data['sku']);
                foreach ($data as $key => $value) {
                    $this->skuProcessor->setNewSkuData($sku, $key, $value);
                }
            }
            $this->updateSkuStorage($newProducts);
        }
        return $this;
    }

    /**
     * Return additional data, needed to select.
     *
     * @return array
     */
    private function getOldSkuFieldsForSelect()
    {
        return ['type_id', 'attribute_set_id'];
    }

    /**
     * Adds newly created products to _oldSku
     *
     * @param array $newProducts
     * @return void
     */
    private function updateSkuStorage(array $newProducts): void
    {
        foreach ($newProducts as $info) {
            $this->skuStorage->set($info);
        }
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
     *
     * @return void
     * @since 100.0.4
     * @deprecated 100.2.3
     * @see \Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor::initMediaGalleryResources
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
        return $this->mediaProcessor->getExistingImages($bunch);
    }

    /**
     * Retrieve image from row.
     *
     * @param array $rowData
     * @return array
     */
    public function getImagesFromRow(array $rowData)
    {
        $images = [];
        $labels = [];
        foreach ($this->_imagesArrayKeys as $column) {
            if (!empty($rowData[$column])) {
                if (is_string($rowData[$column])) {
                    $images[$column] = array_unique(
                        array_map(
                            'trim',
                            explode($this->getMultipleValueSeparator(), $rowData[$column])
                        )
                    );
                } elseif (is_array($rowData[$column])) {
                    $images[$column] = array_unique(
                        array_map(
                            'trim',
                            $rowData[$column]
                        )
                    );
                }
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

    // phpcs:disable Generic.Metrics.NestingLevel

    /**
     * Gather and save information about product entities.
     *
     * FIXME: Reduce nesting level
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws LocalizedException
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    protected function _saveProducts()
    {
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $previousType = null;
        $prevAttributeSet = null;
        $productMediaPath = $this->getProductMediaPath();
        while ($bunch = $this->_dataSourceModel->getNextUniqueBunch($this->getIds())) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $this->websitesCache = [];
            $this->categoriesCache = [];
            $tierPrices = [];
            $mediaGallery = [];
            $labelsForUpdate = [];
            $imagesForChangeVisibility = [];
            $uploadedImages = [];
            $existingImages = $this->getExistingImages($bunch);
            $attributes = [];
            foreach ($bunch as $rowNum => $rowData) {
                try {
                    // reset category processor's failed categories array
                    $this->categoryProcessor->clearFailedCategories();
                    if (!$this->validateRow($rowData, $rowNum)) {
                        continue;
                    }
                    if ($this->getErrorAggregator()->hasToBeTerminated()) {
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                    $rowScope = $this->getRowScope($rowData);
                    $urlKey = $this->getUrlKey($rowData);
                    if (!empty($rowData[self::URL_KEY])) {
                        // If url_key column and its value were in the CSV file
                        $rowData[self::URL_KEY] = $urlKey;
                    } elseif ($this->isNeedToChangeUrlKey($rowData)) {
                        // If url_key column was empty or even not declared in the CSV file but by the rules it needs
                        // to be settled. In case when url_key is generating from name column we have to ensure that
                        // the bunch of products will pass for the event with url_key column.
                        $bunch[$rowNum][self::URL_KEY] = $rowData[self::URL_KEY] = $urlKey;
                    }
                    if (!empty($rowData[self::COL_NAME])) {
                        // remove null byte character
                        $rowData[self::COL_NAME] = preg_replace(self::COL_NAME_FORMAT, '', $rowData[self::COL_NAME]);
                    }
                    $rowSku = $rowData[self::COL_SKU];
                    if (null === $rowSku) {
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                    $storeId = !empty($rowData[self::COL_STORE])
                        ? $this->getStoreIdByCode($rowData[self::COL_STORE])
                        : Store::DEFAULT_STORE_ID;
                    if (self::SCOPE_STORE == $rowScope) {
                        // set necessary data from SCOPE_DEFAULT row
                        $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                        $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                        $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                    }
                    $this->saveProductEntityPhase($rowData, $entityRowsUp, $entityRowsIn);
                    $this->saveProductToWebsitePhase($rowData);
                    $this->saveProductCategoriesPhase($rowNum, $rowData);
                    $this->saveProductTierPricesPhase($rowData, $priceIsGlobal, $tierPrices);
                    $this->saveProductMediaGalleryPhase(
                        $rowNum,
                        $rowData,
                        $storeId,
                        $existingImages,
                        $productMediaPath,
                        $uploadedImages,
                        $imagesForChangeVisibility,
                        $labelsForUpdate,
                        $mediaGallery
                    );
                    $this->saveProductAttributesPhase(
                        $rowData,
                        $rowScope,
                        $previousType,
                        $prevAttributeSet,
                        $attributes
                    );
                    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                } catch (Skip $skip) {
                    // Product is skipped.  Go on to the next one.
                }
            }
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    unset($bunch[$rowNum]);
                }
            }
            $this->saveProductEntity($entityRowsIn, $entityRowsUp);
            $this->_saveProductWebsites($this->websitesCache);
            $this->_saveProductCategories($this->categoriesCache);
            $this->_saveProductTierPrices($tierPrices);
            $this->_saveMediaGallery($mediaGallery);
            $this->updateMediaGalleryVisibility($imagesForChangeVisibility);
            $this->updateMediaGalleryLabels($labelsForUpdate);
            $this->_saveProductAttributes($attributes);
            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                [
                    'adapter' => $this,
                    'bunch' => $bunch,
                    'media_gallery' => $mediaGallery,
                    'media_gallery_labels' => $labelsForUpdate,
                ]
            );
        }
        return $this;
    }
    //phpcs:enable Generic.Metrics.NestingLevel

    // phpcs:enable

    /**
     * In _saveProducts loop, save product entity
     *
     * @param array $rowData
     * @param array $entityRowsUp
     * @param array $entityRowsIn
     * @return void
     * @throws LocalizedException
     */
    private function saveProductEntityPhase(array $rowData, array &$entityRowsUp, array &$entityRowsIn) : void
    {
        $rowSku = $rowData[self::COL_SKU];
        if ($this->isSkuExist($rowSku)) {
            // existing row
            if (isset($rowData['attribute_set_code'])) {
                $attributeSetId = $this->catalogConfig->getAttributeSetId(
                    $this->getEntityTypeId(),
                    $rowData['attribute_set_code']
                );
                // wrong attribute_set_code was received
                if (!$attributeSetId) {
                    throw new LocalizedException(
                        __(
                            'Wrong attribute set code "%1", please correct it and try again.',
                            $rowData['attribute_set_code']
                        )
                    );
                }
            } else {
                $attributeSetId = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
            }
            $entityLinkField = $this->getProductEntityLinkField();
            $entityRowsUp[] = [
                'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                'attribute_set_id' => $attributeSetId,
                $entityLinkField => $this->getExistingSku($rowSku)[$entityLinkField]
            ];
        } else {
            $entityRowsIn[strtolower($rowSku)] = [
                'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                'sku' => $rowSku,
                'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
            ];
        }
    }

    /**
     * In _saveProducts loop, save product to website
     *
     * @param array $rowData
     * @return void
     */
    private function saveProductToWebsitePhase(array $rowData) : void
    {
        $rowSku = $rowData[self::COL_SKU];
        if (!array_key_exists($rowSku, $this->websitesCache)) {
            $this->websitesCache[$rowSku] = [];
        }
        if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
            $websiteCodes = is_string($rowData[self::COL_PRODUCT_WEBSITES])
                ? explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES])
                : (is_array($rowData[self::COL_PRODUCT_WEBSITES])
                    ? $rowData[self::COL_PRODUCT_WEBSITES]
                    : []);

            foreach ($websiteCodes as $websiteCode) {
                $websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCode);
                $this->websitesCache[$rowSku][$websiteId] = true;
            }
        } else {
            $product = $this->retrieveProductBySku($rowSku);
            if ($product) {
                $websiteIds = $product->getWebsiteIds();
                foreach ($websiteIds as $websiteId) {
                    $this->websitesCache[$rowSku][$websiteId] = true;
                }
            }
        }
    }

    /**
     * In _saveProducts loop, save product's categories
     *
     * @param int $rowNum
     * @param array $rowData
     * @return void
     */
    private function saveProductCategoriesPhase(int $rowNum, array $rowData) : void
    {
        $rowSku = $rowData[self::COL_SKU];
        if (!array_key_exists($rowSku, $this->categoriesCache)) {
            $this->categoriesCache[$rowSku] = [];
        }
        $rowData['rowNum'] = $rowNum;
        $categoryIds = $this->processRowCategories($rowData);
        foreach ($categoryIds as $id) {
            $this->categoriesCache[$rowSku][$id] = true;
        }
    }

    /**
     * In _saveProducts loop, save product's tier prices
     *
     * @param array $rowData
     * @param bool $priceIsGlobal
     * @param array $tierPrices
     * @return void
     */
    private function saveProductTierPricesPhase(array $rowData, bool $priceIsGlobal, array &$tierPrices) : void
    {
        $rowSku = $rowData[self::COL_SKU];
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
    }

    /**
     * In _saveProducts loop, save product's media gallery
     *
     * @param int $rowNum
     * @param array $rowData
     * @param int $storeId
     * @param array $existingImages
     * @param string $productMediaPath
     * @param array $uploadedImages
     * @param array $imagesForChangeVisibility
     * @param array $labelsForUpdate
     * @param array $mediaGallery
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    private function saveProductMediaGalleryPhase(
        int $rowNum,
        array &$rowData,
        int $storeId,
        array $existingImages,
        string $productMediaPath,
        array &$uploadedImages,
        array &$imagesForChangeVisibility,
        array &$labelsForUpdate,
        array &$mediaGallery
    ) : void {
        $rowSku = $rowData[self::COL_SKU];
        $rowSkuNormalized = mb_strtolower($rowSku);
        $rowExistingImages = $existingImages[$storeId][$rowSkuNormalized] ?? [];
        $rowExistingImages += $existingImages[Store::DEFAULT_STORE_ID][$rowSkuNormalized] ?? [];
        list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
        $imageHiddenStates = $this->getImagesHiddenStates($rowData);
        $imageHideColumnExist = array_key_exists(self::COL_MEDIA_IMAGE_HIDE, $rowData);
        foreach (array_keys($imageHiddenStates) as $image) {
            //Mark image as uploaded if it exists
            if (array_key_exists($image, $rowExistingImages)) {
                $uploadedImages[$image] = $image;
            }
            //Add image to hide to images list if it does not exist
            if (empty($rowImages[self::COL_MEDIA_IMAGE])
                || !in_array($image, $rowImages[self::COL_MEDIA_IMAGE])
            ) {
                $rowImages[self::COL_MEDIA_IMAGE][] = $image;
            }
        }
        $rowData[self::COL_MEDIA_IMAGE] = [];
        list($rowImages, $rowData) = $this->clearNoSelectionImages($rowImages, $rowData);
        /*
         * Note: to avoid problems with undefined sorting, the value of media gallery items positions
         * must be unique in scope of one product.
         */
        $position = 0;
        $imagesByHash = [];
        foreach ($rowImages as $column => $columnImages) {
            foreach ($columnImages as $columnImageKey => $columnImage) {
                $uploadedFile = $this->findImageByColumnImage(
                    $productMediaPath,
                    $rowExistingImages,
                    $columnImage,
                    $imagesByHash
                );
                if (!$uploadedFile && !isset($uploadedImages[$columnImage])) {
                    $uploadedFile = $this->uploadMediaFiles($columnImage);
                    $uploadedFile = $uploadedFile ?: $this->getSystemFile($columnImage);
                    if ($uploadedFile) {
                        $uploadedImages[$columnImage] = $uploadedFile;
                    } else {
                        unset($rowData[$column]);
                        $this->addRowError(
                            ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                            $rowNum,
                            null,
                            sprintf(
                                $this->_messageTemplates[ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE],
                                $columnImage,
                                $rowNum
                            ),
                            ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                        );
                    }
                } elseif (isset($uploadedImages[$columnImage])) {
                    $uploadedFile = $uploadedImages[$columnImage];
                }
                if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                    $rowData[$column] = $uploadedFile;
                }
                if (!$uploadedFile || isset($mediaGallery[$storeId][$rowSku][$uploadedFile])) {
                    continue;
                }
                $uploadedFileNormalized = ltrim($uploadedFile, '/\\');
                if (isset($rowExistingImages[$uploadedFileNormalized])) {
                    $currentFileData = $rowExistingImages[$uploadedFileNormalized];
                    $currentFileData['store_id'] = $storeId;
                    if ($imageHideColumnExist) {
                        $imagesForChangeVisibility[] = [
                            'disabled' => array_key_exists(
                                $uploadedFile, $imageHiddenStates
                            ) ? $imageHiddenStates[$uploadedFile] : '0',
                            'imageData' => $currentFileData,
                            'exists' => true
                        ];
                    }
                    if (isset($rowLabels[$column][$columnImageKey])
                        && $rowLabels[$column][$columnImageKey] !== $currentFileData['label']
                    ) {
                        $labelsForUpdate[] = [
                            'label' => $rowLabels[$column][$columnImageKey],
                            'imageData' => $currentFileData,
                            'exists' => true
                        ];
                    }
                } else {
                    if ($column === self::COL_MEDIA_IMAGE) {
                        $rowData[$column][] = $uploadedFile;
                    }
                    $mediaGalleryStoreData = [
                        'attribute_id' => $this->getMediaGalleryAttributeId(),
                        'label' => isset($rowLabels[$column][$columnImageKey])
                            ? $rowLabels[$column][$columnImageKey]
                            : '',
                        'position' => ++$position,
                        'disabled' => isset($imageHiddenStates[$columnImage])
                            ? $imageHiddenStates[$columnImage] : '0',
                        'value' => $uploadedFile,
                    ];
                    $mediaGallery[$storeId][$rowSku][$uploadedFile] = $mediaGalleryStoreData;
                    // Add record for default scope if it does not exist
                    if (!($mediaGallery[Store::DEFAULT_STORE_ID][$rowSku][$uploadedFile] ?? [])) {
                        //Set label and disabled values to their default values
                        $mediaGalleryStoreData['label'] = null;
                        $mediaGalleryStoreData['disabled'] = 0;
                        $mediaGallery[Store::DEFAULT_STORE_ID][$rowSku][$uploadedFile] = $mediaGalleryStoreData;
                    }
                }
            }
        }
    }

    /**
     * In _saveProducts loop, save product's attributes
     *
     * @param array $rowData
     * @param int $rowScope
     * @param mixed $previousType
     * @param mixed $prevAttributeSet
     * @param array $attributes
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    private function saveProductAttributesPhase(
        array $rowData,
        int $rowScope,
        &$previousType,
        &$prevAttributeSet,
        array &$attributes
    ) : void {
        $rowSku = $rowData[self::COL_SKU];
        $rowStore = (self::SCOPE_STORE == $rowScope)
            ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
            : 0;
        $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
        if ($productType !== null) {
            $previousType = $productType;
        }
        if (isset($rowData[self::COL_ATTR_SET])) {
            $prevAttributeSet = $rowData[self::COL_ATTR_SET];
        }
        if (self::SCOPE_NULL == $rowScope) {
            // for multiselect attributes only
            if ($prevAttributeSet !== null) {
                $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
            }
            if ($productType === null && $previousType !== null) {
                $productType = $previousType;
            }
            if ($productType === null) {
                throw new Skip(__('Unknown Product Type'));
            }
        }
        $productTypeModel = $this->_productTypeModels[$productType];
        if (isset($rowData['tax_class_name']) && strlen($rowData['tax_class_name'])) {
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
            !$this->isSkuExist($rowSku)
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
            if ('datetime' == $attribute->getBackendType()) {
                $attrValue = trim((string) $attrValue);
                if (!empty($attrValue)) {
                    $timezone = new \DateTimeZone($this->_localeDate->getConfigTimezone());
                    // Parse date from format Y-m-d[ H:i:s]
                    $date = date_create_from_format(DateTime::DATETIME_PHP_FORMAT, $attrValue, $timezone)
                        ?: date_create_from_format(DateTime::DATE_PHP_FORMAT, $attrValue, $timezone);
                    // Perhaps, date is formatted according to user locale. For example, dates in exported csv file
                    $date = $date ?: $this->_localeDate->date($attrValue);
                    if ($attribute->getFrontendInput() === 'date'
                        || in_array($attribute->getAttributeCode(), $this->dateAttrCodes)
                    ) {
                        $date->setTime(0, 0);
                    } else {
                        $date->setTimezone(new \DateTimeZone($this->_localeDate->getDefaultTimezone()));
                    }
                    $attrValue = $date->format(DateTime::DATETIME_PHP_FORMAT);
                } else {
                    $attrValue = null;
                }
            } elseif ($backModel) {
                $attribute->getBackend()->beforeSave($product);
                $attrValue = $product->getData($attribute->getAttributeCode());
            }
            if (self::SCOPE_STORE == $rowScope) {
                if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                    $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                    $storeIds = [$rowStore];
                }
                if (!$this->isSkuExist($rowSku)) {
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

    /**
     * Returns image content by path
     *
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getFileContent(string $path): string
    {
        if ($this->_mediaDirectory->isFile($path)
            && $this->_mediaDirectory->isReadable($path)
        ) {
            return $this->_mediaDirectory->readFile($path);
        }
        return '';
    }

    /**
     * Returns content for remote file
     *
     * @param string $filename
     * @return string
     */
    private function getRemoteFileContent(string $filename): string
    {
        try {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (stripos($filename, self::FILTER_CHAIN) !== false) {
                return '';
            }
            $content = $this->fileDriver->fileGetContents($filename);
        } catch (\Exception $e) {
            $content = false;
        }

        return $content !== false ? $content : '';
    }

    /**
     * Clears entries from Image Set and Row Data marked as no_selection
     *
     * @param array $rowImages
     * @param array $rowData
     * @return array
     */
    private function clearNoSelectionImages($rowImages, $rowData)
    {
        foreach ($rowImages as $column => $columnImages) {
            foreach ($columnImages as $key => $image) {
                if ($image === 'no_selection') {
                    unset($rowImages[$column][$key], $rowData[$column]);
                }
            }
        }

        return [$rowImages, $rowData];
    }

    /**
     * Prepare array with image states (visible or hidden from product page)
     *
     * @param array $rowData
     * @return array
     */
    private function getImagesHiddenStates($rowData)
    {
        $statesArray = [];
        $mappingArray = [
            self::COL_MEDIA_IMAGE_HIDE => '1'
        ];

        foreach ($mappingArray as $key => $value) {
            if (isset($rowData[$key]) && strlen(trim($rowData[$key]))) {
                $items = explode($this->getMultipleValueSeparator(), $rowData[$key]);

                foreach ($items as $item) {
                    $statesArray[$item] = $value;
                }
            }
        }

        return $statesArray;
    }

    /**
     * Resolve valid category ids from provided row data.
     *
     * @param array $rowData
     * @return array
     */
    protected function processRowCategories($rowData)
    {
        $categoryIds = [];
        if (!empty($rowData[self::COL_CATEGORY])) {
            $categoryIds = $this->categoryProcessor->upsertCategories(
                $rowData[self::COL_CATEGORY],
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
        } else {
            $product = $this->retrieveProductBySku($rowData['sku']);
            if ($product) {
                $categoryIds = $product->getCategoryIds();
            }
        }
        return $categoryIds;
    }

    /**
     * Get product websites.
     *
     * @param string $productSku
     * @return array
     */
    public function getProductWebsites($productSku)
    {
        return array_keys($this->websitesCache[$productSku]);
    }

    /**
     * Retrieve product categories.
     *
     * @param string $productSku
     * @return array
     */
    public function getProductCategories($productSku)
    {
        return array_keys($this->categoriesCache[$productSku]);
    }

    /**
     * Get store id by code.
     *
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
     * Returns the import directory if specified or a default import directory (media/import).
     *
     * @return string
     */
    private function getImportDir(): string
    {
        $dirConfig = DirectoryList::getDefaultConfig();
        $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

        return empty($this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR])
            ? $dirAddon . DIRECTORY_SEPARATOR . $this->_mediaDirectory->getRelativePath('import')
            : $this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR];
    }

    /**
     * Returns an object for upload a media files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploader()
    {
        if ($this->_fileUploader === null) {
            $fileUploader = $this->_uploaderFactory->create();

            $fileUploader->init();

            $tmpPath = $this->getImportDir();

            if (is_a($this->_mediaDirectory->getDriver(), AwsS3::class)) {
                if (!$this->_mediaDirectory->create($tmpPath)) {
                    throw new LocalizedException(
                        __('Directory \'%1\' could not be created.', $tmpPath)
                    );
                }
            }

            if (!$fileUploader->setTmpDir($tmpPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }

            $destinationPath = $this->getProductMediaPath();

            $this->_mediaDirectory->create($destinationPath);
            if (!$fileUploader->setDestDir($destinationPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }

            $this->_fileUploader = $fileUploader;
        }
        return $this->_fileUploader;
    }

    /**
     * Retrieve uploader.
     *
     * @return Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUploader()
    {
        return $this->_getUploader();
    }

    /**
     * Uploading files into the "catalog/product" media folder.
     *
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @param bool $renameFileOff [optional] boolean to pass.
     * Default is false which will set not to rename the file after import.
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        try {
            $res = $this->_getUploader()->move($fileName, $renameFileOff);
            return $res['file'];
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }

    /**
     * Try to find file by it's path.
     *
     * @param string $fileName
     * @return string
     */
    private function getSystemFile($fileName)
    {
        $filePath = $this->joinFilePaths($this->getProductMediaPath(), $fileName);

        return $this->_mediaDirectory->isFile($filePath) && $this->_mediaDirectory->isReadable($filePath)
            ? $fileName
            : '';
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
        $this->mediaProcessor->saveMediaGallery($mediaGalleryData);

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
        while ($bunch = $this->_dataSourceModel->getNextUniqueBunch($this->getIds())) {
            $stockData = [];
            $importedData = [];
            $productIdsToReindex = [];
            $stockChangedProductIds = [];
            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $row = [];
                $sku = $rowData[self::COL_SKU];
                if ($this->skuProcessor->getNewSku($sku) !== null) {
                    $stockItem = $this->getRowExistingStockItem($rowData);
                    $existingStockItemData = $stockItem->getData();
                    $row = $this->formatStockDataForRow($rowData);
                    $productIdsToReindex[] = $row['product_id'];
                    $storeId = $this->getRowStoreId($rowData);
                    if (!empty(array_diff_assoc($row, $existingStockItemData))
                        || $this->statusProcessor->isStatusChanged($sku, $storeId)
                    ) {
                        $stockChangedProductIds[] = $row['product_id'];
                    }
                }

                if (!isset($stockData[$sku])) {
                    $stockData[$sku] = $row;
                    $importedData[$sku] = $rowData;
                }
            }

            // Insert rows
            if (!empty($stockData)) {
                $this->stockItemProcessor->process($stockData, $importedData);
            }

            $this->reindexStockStatus($stockChangedProductIds);
            $this->reindexProducts($productIdsToReindex);
        }
        return $this;
    }

    /**
     * Reindex stock status for provided product IDs
     *
     * @param array $productIds
     */
    private function reindexStockStatus(array $productIds): void
    {
        if ($productIds) {
            $this->stockProcessor->reindexList($productIds);
        }
    }

    /**
     * Initiate product reindex by product ids
     *
     * @param array $productIdsToReindex
     * @return void
     */
    private function reindexProducts($productIdsToReindex = [])
    {
        if (is_array($productIdsToReindex) && !empty($productIdsToReindex)) {
            $indexersToReindex = [
                ProductCategoryIndexer::INDEXER_ID,
                ProductPriceIndexer::INDEXER_ID
            ];
            foreach ($indexersToReindex as $id) {
                $indexer = $this->indexerRegistry->get($id);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexList($productIdsToReindex);
                }
            }
        }
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $attrCode
     * @return mixed
     */
    public function retrieveAttributeByCode($attrCode)
    {
        /** @var string $attrCode */
        $attrCode = mb_strtolower($attrCode);

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
     * Returns array of new products data with SKU as key. All SKU keys are in lowercase for avoiding creation of
     * new products with the same SKU in different letter cases.
     *
     * @param string $sku
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
        return $this->_dataSourceModel->getNextUniqueBunch($this->getIds());
    }

    /**
     * Existing products SKU getter.
     *
     * Returns array of existing products data with SKU as key. All SKU keys are in lowercase for avoiding creation of
     * new products with the same SKU in different letter cases.
     *
     * @return array
     * @deprecated This method is deprecated due to high memory consumption.
     * @see SkuStorage
     */
    public function getOldSku()
    {
        // For backward compatibility get all data from storage
        $oldSkus = [];
        foreach ($this->skuStorage->iterate() as $sku => $value) {
            $oldSkus[$sku] = $value;
            $oldSkus[$sku]['supported_type'] = isset($this->_productTypeModels[$value['type_id']]);
        }

        return $oldSkus;
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
     * @throws \Zend_Validate_Exception
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        $rowScope = $this->getRowScope($rowData);
        $sku = $rowData[self::COL_SKU];

        // BEHAVIOR_DELETE and BEHAVIOR_REPLACE use specific validation logic
        if (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !$this->isSkuExist($sku)) {
                $this->skipRow($rowNum, ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE);
                return false;
            }
        }
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !$this->isSkuExist($sku)) {
                $this->skipRow($rowNum, ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE);
                return false;
            }
            return true;
        }

        // if product doesn't exist, need to throw critical error else all errors should be not critical.
        $errorLevel = $this->getValidationErrorLevel($sku);

        if (!$this->validator->isValid($rowData)) {
            foreach ($this->validator->getMessages() as $message) {
                $this->skipRow($rowNum, $message, $errorLevel, $this->validator->getInvalidAttribute());
            }
        }

        if (null === $sku) {
            $this->skipRow($rowNum, ValidatorInterface::ERROR_SKU_IS_EMPTY, $errorLevel);
        } elseif (false === $sku) {
            $this->skipRow($rowNum, ValidatorInterface::ERROR_ROW_IS_ORPHAN, $errorLevel);
        } elseif (self::SCOPE_STORE == $rowScope
            && !$this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
        ) {
            $this->skipRow($rowNum, ValidatorInterface::ERROR_INVALID_STORE, $errorLevel);
        }

        // SKU is specified, row is SCOPE_DEFAULT, new product block begins
        $this->_processedEntitiesCount++;

        if ($this->isSkuExist($sku) && Import::BEHAVIOR_REPLACE !== $this->getBehavior()) {
            // can we get all necessary data from existent DB product?
            // check for supported type of existing product
            if (isset($this->_productTypeModels[$this->getExistingSku($sku)['type_id']])) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    $this->prepareNewSkuData($sku)
                );
            } else {
                $this->skipRow($rowNum, ValidatorInterface::ERROR_TYPE_UNSUPPORTED, $errorLevel);
            }
        } else {
            // validate new product type and attribute set
            if (!isset($rowData[self::COL_TYPE], $this->_productTypeModels[$rowData[self::COL_TYPE]])) {
                $this->skipRow($rowNum, ValidatorInterface::ERROR_INVALID_TYPE, $errorLevel);
            } elseif (!isset($rowData[self::COL_ATTR_SET], $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]])
            ) {
                $this->skipRow($rowNum, ValidatorInterface::ERROR_INVALID_ATTR_SET, $errorLevel);
            } elseif ($this->skuProcessor->getNewSku($sku) === null) {
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
        }

        if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
            $newSku = $this->skuProcessor->getNewSku($sku);
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];

            /** @var AbstractType $productTypeValidator */
            // isRowValid can add error to general errors pull if row is invalid
            $productTypeValidator = $this->_productTypeModels[$newSku['type_id']];
            $productTypeValidator->isRowValid(
                $rowData,
                $rowNum,
                !($this->isSkuExist($sku) && Import::BEHAVIOR_REPLACE !== $this->getBehavior())
            );
        }
        // validate custom options
        $this->getOptionEntity()->validateRow($rowData, $rowNum);

        if ($this->isNeedToValidateUrlKey($rowData)) {
            $urlKey = strtolower($this->getUrlKey($rowData));
            $storeCodes = empty($rowData[self::COL_STORE_VIEW_CODE])
                ? array_flip($this->storeResolver->getStoreCodeToId())
                : explode($this->getMultipleValueSeparator(), $rowData[self::COL_STORE_VIEW_CODE]);
            foreach ($storeCodes as $storeCode) {
                $storeId = $this->storeResolver->getStoreCodeToId($storeCode);
                $productUrlSuffix = $this->getProductUrlSuffix($storeId);
                $urlPath = $urlKey . $productUrlSuffix;
                if (empty($this->urlKeys[$storeId][$urlPath])
                    || ($this->urlKeys[$storeId][$urlPath] == $sku)
                ) {
                    $this->urlKeys[$storeId][$urlPath] = $sku;
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
                        $urlKey,
                        $message,
                        $errorLevel
                    )
                        ->getErrorAggregator()
                        ->addRowToSkip($rowNum);
                }
            }
        }

        if (!empty($rowData['new_from_date']) && !empty($rowData['new_to_date'])
        ) {
            $newFromTimestamp = strtotime($this->dateTime->formatDate($rowData['new_from_date'], false));
            $newToTimestamp = strtotime($this->dateTime->formatDate($rowData['new_to_date'], false));
            if ($newFromTimestamp > $newToTimestamp) {
                $this->skipRow(
                    $rowNum,
                    'invalidNewToDateValue',
                    $errorLevel,
                    $rowData['new_to_date']
                );
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Check if need to validate url key.
     *
     * @param array $rowData
     * @return bool
     */
    private function isNeedToValidateUrlKey($rowData)
    {
        if (!empty($rowData[self::COL_SKU]) && empty($rowData[self::URL_KEY])
            && $this->getBehavior() === Import::BEHAVIOR_APPEND
            && $this->isSkuExist($rowData[self::COL_SKU])) {
            return false;
        }

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
        foreach ($this->getExistingSku($sku) as $key => $value) {
            $data[$key] = $value;
        }

        $data['attr_set_code'] = $this->_attrSetIdToName[$this->getExistingSku($sku)['attr_set_id']];

        return $data;
    }

    /**
     * Parse attributes names and values string to array.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _parseAdditionalAttributes(array $rowData): array
    {
        if (empty($rowData['additional_attributes'])) {
            return $rowData;
        }
        if (is_array($rowData['additional_attributes'])) {
            foreach ($rowData['additional_attributes'] as $key => $value) {
                $rowData[mb_strtolower($key)] = $value;
            }
        } else {
            $rowData = array_merge($rowData, $this->getAdditionalAttributes($rowData));
        }
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
     * @param array $rowData
     * @return array
     */
    private function getAdditionalAttributes(array $rowData): array
    {
        return empty($this->_parameters[Import::FIELDS_ENCLOSURE])
            ? $this->parseAttributesWithoutWrappedValues($rowData['additional_attributes'], $rowData['product_type'])
            : $this->parseAttributesWithWrappedValues($rowData['additional_attributes']);
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
     * @param string $productType
     * @return array
     */
    private function parseAttributesWithoutWrappedValues(string $attributesData, string $productType): array
    {
        $attributeNameValuePairs = explode($this->getMultipleValueSeparator(), $attributesData);
        $preparedAttributes = [];
        $code = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if ($attributeData === null || strpos($attributeData, self::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (!$code) {
                    continue;
                }
                $preparedAttributes[$code] .= $this->getMultipleValueSeparator() . $attributeData;
                continue;
            }
            list($code, $value) = explode(self::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
            $code = mb_strtolower($code);

            $entityTypeModel = $this->retrieveProductTypeByName($productType);
            if ($entityTypeModel) {
                $attrParams = $entityTypeModel->retrieveAttributeFromCache($code);
                if (!empty($attrParams) && $attrParams['type'] ==  'multiselect') {
                    $parsedValue = $this->parseMultiselectValues($value, self::PSEUDO_MULTI_LINE_SEPARATOR);
                    if (count($parsedValue) > 1) {
                        $value = $parsedValue;
                    }
                }
            }
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
        preg_match_all(
            '~((?:[a-zA-Z0-9_])+)="((?:[^"]|""|"' . $this->getMultiLineSeparatorForRegexp() . '")+)"+~',
            $attributesData,
            $matches
        );
        foreach ($matches[1] as $i => $attributeCode) {
            $attribute = $this->retrieveAttributeByCode($attributeCode);
            $value = 'multiselect' != $attribute->getFrontendInput()
                ? str_replace('""', '"', $matches[2][$i])
                : '"' . $matches[2][$i] . '"';
            $attributes[mb_strtolower($attributeCode)] = $value;
        }
        return $attributes;
    }

    /**
     * Parse values of multiselect attributes depends on "Fields Enclosure" parameter
     *
     * @param string $values
     * @param string $delimiter
     * @return array
     * @since 100.1.2
     */
    public function parseMultiselectValues($values, $delimiter = '')
    {
        if (empty($this->_parameters[Import::FIELDS_ENCLOSURE])) {
            if (is_array($values)) {
                return $values;
            }
            if (!$delimiter) {
                $delimiter = $this->getMultipleValueSeparator();
            }

            return $values !== null ? explode($delimiter, $values) : [];
        }
        if (preg_match_all('~"((?:[^"]|"")*)"~', $values, $matches)) {
            return $values = array_map(
                function ($value) {
                    return str_replace('""', '"', $value);
                },
                $matches[1]
            );
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
            $useConfigName = $key === StockItemInterface::ENABLE_QTY_INCREMENTS
                ? StockItemInterface::USE_CONFIG_ENABLE_QTY_INC
                : self::INVENTORY_USE_CONFIG_PREFIX . $key;

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
     * @return $this|AbstractEntity
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $source->rewind();

        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
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
     * @since 100.0.3
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
                )->where('request_path IN (?)', array_map('strval', array_keys($urlKeys)))
                    ->where('store_id IN (?)', $storeId)
                    ->where('cpe.sku not in (?)', array_values($urlKeys))
            );
            foreach ($urlKeyDuplicates as $entityData) {
                $rowNum = $this->rowNumbers[$entityData['store_id']][$entityData['request_path']];
                $message = sprintf(
                    $this->retrieveMessageTemplate(ValidatorInterface::ERROR_DUPLICATE_URL_KEY),
                    $entityData['request_path'],
                    $entityData['sku']
                );
                $this->addRowError(ValidatorInterface::ERROR_DUPLICATE_URL_KEY, $rowNum, 'url_key', $message);
            }
        }
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     * @since 100.0.3
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
     * Retrieve url key from provided row data.
     *
     * @param array $rowData
     * @return string
     *
     * @since 100.0.3
     */
    protected function getUrlKey($rowData)
    {
        if (!empty($rowData[self::URL_KEY])) {
            $urlKey = (string) $rowData[self::URL_KEY];
            return $this->productUrl->formatUrlKey($urlKey);
        }

        if (!empty($rowData[self::COL_NAME])
            && (array_key_exists(self::URL_KEY, $rowData) || !$this->isSkuExist($rowData[self::COL_SKU]))) {
            return $this->productUrl->formatUrlKey($rowData[self::COL_NAME]);
        }

        return '';
    }

    /**
     * Retrieve resource.
     *
     * @return Proxy\Product\ResourceModel
     *
     * @since 100.0.3
     */
    protected function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = $this->_resourceFactory->create();
        }
        return $this->_resource;
    }

    /**
     * Whether a url key needs to change.
     *
     * @param array $rowData
     * @return bool
     */
    private function isNeedToChangeUrlKey(array $rowData): bool
    {
        $urlKey = $this->getUrlKey($rowData);
        $productExists = $this->isSkuExist($rowData[self::COL_SKU]);
        $markedToEraseUrlKey = isset($rowData[self::URL_KEY]);
        // The product isn't new and the url key index wasn't marked for change.
        if (!$urlKey && $productExists && !$markedToEraseUrlKey) {
            // Seems there is no need to change the url key
            return false;
        }

        return true;
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
        if (!empty($labels)) {
            $this->mediaProcessor->updateMediaGalleryLabels($labels);
        }
    }

    /**
     * Update 'disabled' field for media gallery entity
     *
     * @param array $images
     * @return $this
     */
    private function updateMediaGalleryVisibility(array $images)
    {
        if (!empty($images)) {
            $this->mediaProcessor->updateMediaGalleryVisibility($images);
        }

        return $this;
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

    /**
     * Check if product exists for specified SKU
     *
     * @param string $sku
     * @return bool
     */
    private function isSkuExist($sku)
    {
        if ($sku !== null) {
            return $this->skuStorage->has($sku);
        }
        return false;
    }

    /**
     * Get existing product data for specified SKU
     *
     * @param string $sku
     * @return array
     */
    private function getExistingSku($sku)
    {
        return $this->skuStorage->get((string)$sku);
    }

    /**
     * Format row data to DB compatible values.
     *
     * @param array $rowData
     * @return array
     */
    private function formatStockDataForRow(array $rowData): array
    {
        $sku = $rowData[self::COL_SKU];
        $row['product_id'] = $this->skuProcessor->getNewSku($sku)['entity_id'];
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

        if ($this->stockConfiguration->isQty($this->skuProcessor->getNewSku($sku)['type_id'])) {
            $stockItemDo->setData($row);
            $row['is_in_stock'] = $this->stockStateProvider->verifyStock($stockItemDo)
                ? (int) $row['is_in_stock']
                : 0;
            if ($this->stockStateProvider->verifyNotification($stockItemDo)) {
                $date = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
                $row['low_stock_date'] = $date->format(DateTime::DATETIME_PHP_FORMAT);
            }
            $row['stock_status_changed_auto'] = (int)!$this->stockStateProvider->verifyStock($stockItemDo);
        } else {
            $row['qty'] = 0;
        }

        return $row;
    }

    /**
     * Retrieve product by sku.
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function retrieveProductBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $product;
    }

    /**
     * Add row as skipped
     *
     * @param int $rowNum
     * @param string $errorCode Error code or simply column name
     * @param string $errorLevel error level
     * @param string|null $colName optional column name
     * @return $this
     */
    private function skipRow(
        $rowNum,
        string $errorCode,
        string $errorLevel = ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
        $colName = null
    ): self {
        $this->addRowError($errorCode, $rowNum, $colName, null, $errorLevel);
        $this->getErrorAggregator()
            ->addRowToSkip($rowNum);
        return $this;
    }

    /**
     * Returns errorLevel for validation
     *
     * @param string $sku
     * @return string
     */
    private function getValidationErrorLevel($sku): string
    {
        return (!$this->isSkuExist($sku) && Import::BEHAVIOR_REPLACE !== $this->getBehavior())
            ? ProcessingError::ERROR_LEVEL_CRITICAL
            : ProcessingError::ERROR_LEVEL_NOT_CRITICAL;
    }

    /**
     * Get row store ID
     *
     * @param array $rowData
     * @return int
     */
    private function getRowStoreId(array $rowData): int
    {
        return !empty($rowData[self::COL_STORE])
            ? (int) $this->getStoreIdByCode($rowData[self::COL_STORE])
            : Store::DEFAULT_STORE_ID;
    }

    /**
     * Get row stock item model
     *
     * @param array $rowData
     * @return StockItemInterface
     */
    private function getRowExistingStockItem(array $rowData): StockItemInterface
    {
        $productId = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
        $websiteId = $this->stockConfiguration->getDefaultScopeId();
        return $this->stockRegistry->getStockItem($productId, $websiteId);
    }

    /**
     * Returns image that matches the provided image content
     *
     * @param string $productMediaPath
     * @param array $images
     * @param string $columnImage
     * @param array $imagesByHash
     * @return string
     */
    private function findImageByColumnImage(
        string $productMediaPath,
        array &$images,
        string $columnImage,
        array &$imagesByHash
    ): string {
        $content = filter_var($columnImage, FILTER_VALIDATE_URL)
            ? $this->getRemoteFileContent($columnImage)
            : $this->getFileContent($this->joinFilePaths($this->getUploader()->getTmpDir(), $columnImage));
        if (!$content) {
            return '';
        }
        return $this->findImageByColumnImageUsingHash($productMediaPath, $images, $content, $imagesByHash);
    }

    /**
     * Returns image that matches the provided image content using hash
     *
     * @param string $productMediaPath
     * @param array $images
     * @param string $content
     * @param array $imagesByHash
     * @return string
     */
    private function findImageByColumnImageUsingHash(
        string $productMediaPath,
        array &$images,
        string $content,
        array &$imagesByHash
    ): string {
        $hash = hash($this->hashAlgorithm, $content);
        if (!empty($imagesByHash[$hash])) {
            return $imagesByHash[$hash];
        }
        foreach ($images as &$image) {
            if (!isset($image['hash'])) {
                $imageContent = $this->getFileContent($this->joinFilePaths($productMediaPath, $image['value']));
                if (!$imageContent) {
                    $image['hash'] = '';
                    continue;
                }
                $image['hash'] = hash($this->hashAlgorithm, $imageContent);
                $imagesByHash[$image['hash']] = $image['value'];
            }
            if (!empty($image['hash']) && $image['hash'] === $hash) {
                return $image['value'];
            }
        }
        return '';
    }

    /**
     * Returns product media
     *
     * @return string relative path to root folder
     */
    private function getProductMediaPath(): string
    {
        return $this->joinFilePaths($this->getMediaBasePath(), 'catalog', 'product');
    }

    /**
     * Returns media base path
     *
     * @return string relative path to root folder
     */
    private function getMediaBasePath(): string
    {
        $mediaDir = !is_a($this->_mediaDirectory->getDriver(), File::class)
            // make media folder a primary folder for media in external storages
            ? $this->filesystem->getDirectoryReadByPath(DirectoryList::MEDIA)
            : $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        return $this->_mediaDirectory->getRelativePath($mediaDir->getAbsolutePath());
    }

    /**
     * Joins two paths and remove redundant directory separator
     *
     * @param array $paths
     * @return string
     */
    private function joinFilePaths(...$paths): string
    {
        $result = '';
        if ($paths) {
            $firstPath = array_shift($paths);
            $result = $firstPath !== null ? rtrim($firstPath, DIRECTORY_SEPARATOR) : '';
            foreach ($paths as $path) {
                $result .= DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            }
        }
        return $result;
    }
}
