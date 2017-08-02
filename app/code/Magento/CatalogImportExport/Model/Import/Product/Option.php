<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as ProductOptionValueCollection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as ProductOptionValueCollectionFactory;

/**
 * Entity class which provide possibility to import product custom options
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Option extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    /**#@+
     * Custom option column names
     */
    const COLUMN_SKU = 'sku';

    const COLUMN_PREFIX = '_custom_option_';

    const COLUMN_STORE = '_custom_option_store';

    const COLUMN_TYPE = '_custom_option_type';

    const COLUMN_TITLE = '_custom_option_title';

    const COLUMN_IS_REQUIRED = '_custom_option_is_required';

    const COLUMN_SORT_ORDER = '_custom_option_sort_order';

    const COLUMN_ROW_TITLE = '_custom_option_row_title';

    const COLUMN_ROW_PRICE = '_custom_option_row_price';

    const COLUMN_ROW_SKU = '_custom_option_row_sku';

    const COLUMN_ROW_SORT = '_custom_option_row_sort';

    /**#@-*/

    /**
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'import/format_v1/page_size';

    /**
     * @var string
     * @since 2.1.0
     */
    private $columnMaxCharacters = '_custom_option_max_characters';

    /**
     * All stores code-ID pairs
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeCodeToId = [];

    /**
     * List of products sku-ID pairs
     *
     * @var array
     * @since 2.0.0
     */
    protected $_productsSkuToId = [];

    /**
     * Instance of import/export resource helper
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     * @since 2.0.0
     */
    protected $_resourceHelper;

    /**
     * Flag for global prices property
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isPriceGlobal;

    /**
     * List of specific custom option types
     *
     * @var array
     * @since 2.0.0
     */
    protected $_specificTypes = [
        'date' => ['price', 'sku'],
        'date_time' => ['price', 'sku'],
        'time' => ['price', 'sku'],
        'field' => ['price', 'sku', 'max_characters'],
        'area' => ['price', 'sku', 'max_characters'],
        'drop_down' => true,
        'radio' => true,
        'checkbox' => true,
        'multiple' => true,
        'file' => ['sku', 'file_extension', 'image_size_x', 'image_size_y'],
    ];

    /**
     * Keep product id value for every row which will be imported
     *
     * @var int
     * @since 2.0.0
     */
    protected $_rowProductId;

    /**
     * Keep product sku value for every row during validation
     *
     * @var string
     * @since 2.0.0
     */
    protected $_rowProductSku;

    /**
     * Keep store id value for every row which will be imported
     *
     * @var int
     * @since 2.0.0
     */
    protected $_rowStoreId;

    /**
     * Keep information about row status
     *
     * @var int
     * @since 2.0.0
     */
    protected $_rowIsMain;

    /**
     * Keep type value for every row which will be imported
     *
     * @var int
     * @since 2.0.0
     */
    protected $_rowType;

    /**
     * Product model instance
     *
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_productModel;

    /**
     * DB data source model
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     * @since 2.0.0
     */
    protected $_dataSourceModel;

    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $_connection;

    /**
     * Custom options tables
     *
     * @var array
     * @since 2.0.0
     */
    protected $_tables = [
        'catalog_product_entity' => null,
        'catalog_product_option' => null,
        'catalog_product_option_title' => null,
        'catalog_product_option_type_title' => null,
        'catalog_product_option_type_value' => null,
        'catalog_product_option_type_price' => null,
        'catalog_product_option_price' => null,
    ];

    /**
     * Parent import product entity
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product
     * @since 2.0.0
     */
    protected $_productEntity;

    /**
     * Existing custom options data
     *
     * @var array
     * @since 2.0.0
     */
    protected $_oldCustomOptions;

    /**
     * New custom options data for existing products
     *
     * @var array
     * @since 2.0.0
     */
    protected $_newOptionsOldData = [];

    /**
     * New custom options data for not existing products
     *
     * @var array
     * @since 2.0.0
     */
    protected $_newOptionsNewData = [];

    /**
     * New custom options counter
     *
     * @var int
     * @since 2.0.0
     */
    protected $_newCustomOptionId = 0;

    /**
     * Product options collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     * @since 2.0.0
     */
    protected $_optionCollection;

    /**#@+
     * Error codes
     */
    const ERROR_INVALID_STORE = 'optionInvalidStore';

    const ERROR_INVALID_TYPE = 'optionInvalidType';

    const ERROR_EMPTY_TITLE = 'optionEmptyTitle';

    const ERROR_INVALID_PRICE = 'optionInvalidPrice';

    const ERROR_INVALID_MAX_CHARACTERS = 'optionInvalidMaxCharacters';

    const ERROR_INVALID_SORT_ORDER = 'optionInvalidSortOrder';

    const ERROR_INVALID_ROW_PRICE = 'optionInvalidRowPrice';

    const ERROR_INVALID_ROW_SORT = 'optionInvalidRowSort';

    const ERROR_AMBIGUOUS_NEW_NAMES = 'optionAmbiguousNewNames';

    const ERROR_AMBIGUOUS_OLD_NAMES = 'optionAmbiguousOldNames';

    const ERROR_AMBIGUOUS_TYPES = 'optionAmbiguousTypes';

    /**#@-*/

    /**
     * Collection by pages iterator
     *
     * @var \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
     * @since 2.0.0
     */
    protected $_byPagesIterator;

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     * @since 2.0.0
     */
    protected $_pageSize;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     * @since 2.0.0
     */
    protected $_catalogData = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     * @since 2.0.0
     */
    protected $_importFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory
     * @since 2.0.0
     */
    protected $_optionColFactory;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory
     * @since 2.0.0
     */
    protected $_colIteratorFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * Product entity link field
     *
     * @var string
     * @since 2.1.0
     */
    private $productEntityLinkField;

    /**
     * Product entity identifier field
     *
     * @var string
     * @since 2.1.0
     */
    private $productEntityIdentifierField;

    /**
     * @var ProductOptionValueCollectionFactory
     */
    private $productOptionValueCollectionFactory;

    /**
     * @var array
     */
    private $optionTypeTitles;

    /**
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ProductOptionValueCollectionFactory $productOptionValueCollectionFactory
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ProductOptionValueCollectionFactory $productOptionValueCollectionFactory = null,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_catalogData = $catalogData;
        $this->_storeManager = $_storeManager;
        $this->_productFactory = $productFactory;
        $this->_dataSourceModel = $importData;
        $this->_optionColFactory = $optionColFactory;
        $this->_colIteratorFactory = $colIteratorFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->productOptionValueCollectionFactory = $productOptionValueCollectionFactory
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ProductOptionValueCollectionFactory::class);

        if (isset($data['connection'])) {
            $this->_connection = $data['connection'];
        } else {
            $this->_connection = $resource->getConnection();
        }

        if (isset($data['resource_helper'])) {
            $this->_resourceHelper = $data['resource_helper'];
        } else {
            $this->_resourceHelper = $resourceHelper;
        }

        if (isset($data['is_price_global'])) {
            $this->_isPriceGlobal = $data['is_price_global'];
        } else {
            $this->_isPriceGlobal = $this->_catalogData->isPriceGlobal();
        }

        /**
         * TODO: Make metadataPool a direct constructor dependency, and eliminate its setter & getter
         */
        if (isset($data['metadata_pool'])) {
            $this->metadataPool = $data['metadata_pool'];
        }

        $this->errorAggregator = $errorAggregator;

        $this->_initSourceEntities($data)->_initTables($data)->_initStores($data);

        $this->_initMessageTemplates();

        $this->_initProductsSku()->_initOldCustomOptions();
    }

    /**
     * Initialization of error message templates
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initMessageTemplates()
    {
        // @codingStandardsIgnoreStart
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_STORE,
            __('Value for \'price\' sub attribute in \'store\' attribute contains incorrect value')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_TYPE,
            __('Value for \'type\' sub attribute in \'custom_options\' attribute contains incorrect value, acceptable values are: \'dropdown\', \'checkbox\'')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_EMPTY_TITLE, __('Please enter a value for title.'));
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_PRICE,
            __('Value for \'price\' sub attribute in \'custom_options\' attribute contains incorrect value')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_MAX_CHARACTERS,
            __('Value for \'maximum characters\' sub attribute in \'custom_options\' attribute contains incorrect value')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_SORT_ORDER,
            __('Value for \'sort order\' sub attribute in \'custom_options\' attribute contains incorrect value')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_ROW_PRICE,
            __('Value for \'value price\' sub attribute in \'custom_options\' attribute contains incorrect value')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_INVALID_ROW_SORT,
            __('Value for \'sort order\' sub attribute in \'custom_options\' attribute contains incorrect value')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_AMBIGUOUS_NEW_NAMES,
            __('This name is already being used for custom option. Please enter a different name.')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_AMBIGUOUS_OLD_NAMES,
            __('This name is already being used for custom option. Please enter a different name.')
        );
        $this->_productEntity->addMessageTemplate(
            self::ERROR_AMBIGUOUS_TYPES,
            __('Custom options have different types.')
        );
        // @codingStandardsIgnoreEnd
        return $this;
    }

    /**
     * Initialize table names
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    protected function _initTables(array $data)
    {
        if (isset($data['tables'])) {
            // all the entries of $data['tables'] which have keys that are present in $this->_tables
            $tables = array_intersect_key($data['tables'], $this->_tables);
            $this->_tables = array_merge($this->_tables, $tables);
        }
        foreach ($this->_tables as $key => $value) {
            if ($value == null) {
                $this->_tables[$key] = $this->_resource->getTableName($key);
            }
        }
        return $this;
    }

    /**
     * Initialize stores data
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    protected function _initStores(array $data)
    {
        if (isset($data['stores'])) {
            $this->_storeCodeToId = $data['stores'];
        } else {
            /** @var $store \Magento\Store\Model\Store */
            foreach ($this->_storeManager->getStores(true) as $store) {
                $this->_storeCodeToId[$store->getCode()] = $store->getId();
            }
        }
        return $this;
    }

    /**
     * Initialize source entities and collections
     *
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _initSourceEntities(array $data)
    {
        if (isset($data['data_source_model'])) {
            $this->_dataSourceModel = $data['data_source_model'];
        }
        if (isset($data['product_model'])) {
            $this->_productModel = $data['product_model'];
        } else {
            $this->_productModel = $this->_productFactory->create();
        }
        if (isset($data['option_collection'])) {
            $this->_optionCollection = $data['option_collection'];
        } else {
            $this->_optionCollection = $this->_optionColFactory->create();
        }
        if (isset($data['product_entity'])) {
            $this->_productEntity = $data['product_entity'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Every option entity must have a parent product entity.')
            );
        }
        if (isset($data['collection_by_pages_iterator'])) {
            $this->_byPagesIterator = $data['collection_by_pages_iterator'];
        } else {
            $this->_byPagesIterator = $this->_colIteratorFactory->create();
        }
        if (isset($data['page_size'])) {
            $this->_pageSize = $data['page_size'];
        } else {
            $this->_pageSize = self::XML_PATH_PAGE_SIZE ? (int)$this->_scopeConfig->getValue(
                self::XML_PATH_PAGE_SIZE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) : 0;
        }
        return $this;
    }

    /**
     * Load exiting custom options data
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initOldCustomOptions()
    {
        if (!$this->_oldCustomOptions) {
            $oldCustomOptions = [];
            $optionTitleTable = $this->_tables['catalog_product_option_title'];
            foreach ($this->_storeCodeToId as $storeId) {
                $addCustomOptions = function (
                    \Magento\Catalog\Model\Product\Option $customOption
                ) use (
                    &$oldCustomOptions,
                    $storeId
                ) {
                    $productId = $customOption->getProductId();
                    if (!isset($oldCustomOptions[$productId])) {
                        $oldCustomOptions[$productId] = [];
                    }
                    if (isset($oldCustomOptions[$productId][$customOption->getId()])) {
                        $oldCustomOptions[$productId][$customOption->getId()]['titles'][$storeId] = $customOption
                            ->getTitle();
                    } else {
                        $oldCustomOptions[$productId][$customOption->getId()] = [
                            'titles' => [$storeId => $customOption->getTitle()],
                            'type' => $customOption->getType(),
                        ];
                    }
                };
                /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Option\Collection */
                $this->_optionCollection->reset();
                $this->_optionCollection->getSelect()->join(
                    ['option_title' => $optionTitleTable],
                    'option_title.option_id = main_table.option_id',
                    ['title' => 'title', 'store_id' => 'store_id']
                )->where(
                    'option_title.store_id = ?',
                    $storeId
                );

                $this->_byPagesIterator->iterate($this->_optionCollection, $this->_pageSize, [$addCustomOptions]);
            }
            $this->_oldCustomOptions = $oldCustomOptions;
        }
        return $this;
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityTypeCode()
    {
        return 'product_options';
    }

    /**
     * Validate ambiguous situations:
     * - several custom options have the same name in input file;
     * - several custom options have the same name in DB;
     * - custom options with the same name have different data types.
     *
     * @return bool
     * @since 2.0.0
     */
    public function validateAmbiguousData()
    {
        $errorRows = $this->_findNewOptionsWithTheSameTitles();
        if ($errorRows) {
            $this->_addRowsErrors(self::ERROR_AMBIGUOUS_NEW_NAMES, $errorRows);
            return false;
        }
        if ($this->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
            $errorRows = $this->_findOldOptionsWithTheSameTitles();
            if ($errorRows) {
                $this->_addRowsErrors(self::ERROR_AMBIGUOUS_OLD_NAMES, $errorRows);
                return false;
            }
            $errorRows = $this->_findNewOldOptionsTypeMismatch();
            if ($errorRows) {
                $this->_addRowsErrors(self::ERROR_AMBIGUOUS_TYPES, $errorRows);
                return false;
            }
        }
        return true;
    }

    /**
     * Find options with the same titles for input data
     *
     * @return array
     * @since 2.0.0
     */
    protected function _findNewOptionsWithTheSameTitles()
    {
        $errorRows = array_unique(
            array_merge(
                $this->_getNewOptionsWithTheSameTitlesErrorRows($this->_newOptionsNewData),
                $this->_getNewOptionsWithTheSameTitlesErrorRows($this->_newOptionsOldData)
            )
        );
        sort($errorRows);
        return $errorRows;
    }

    /**
     * Get error rows numbers for required product data
     *
     * @param array $sourceProductData
     * @return array
     * @since 2.0.0
     */
    protected function _getNewOptionsWithTheSameTitlesErrorRows(array $sourceProductData)
    {
        $errorRows = [];
        foreach ($sourceProductData as $options) {
            foreach ($options as $outerKey => $outerData) {
                foreach ($options as $innerKey => $innerData) {
                    if ($innerKey != $outerKey) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles) {
                                $errorRows = array_merge($errorRows, $innerData['rows'], $outerData['rows']);
                            }
                        }
                    }
                }
            }
        }
        return $errorRows;
    }

    /**
     * Find options with the same titles in DB
     *
     * @return array
     * @since 2.0.0
     */
    protected function _findOldOptionsWithTheSameTitles()
    {
        $errorRows = [];
        foreach ($this->_newOptionsOldData as $productId => $options) {
            foreach ($options as $outerData) {
                if (isset($this->_oldCustomOptions[$productId])) {
                    $optionsCount = 0;
                    foreach ($this->_oldCustomOptions[$productId] as $innerData) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles) {
                                $optionsCount++;
                            }
                        }
                    }
                    if ($optionsCount > 1) {
                        $errorRows = array_merge($errorRows, $outerData['rows']);
                    }
                }
            }
        }
        sort($errorRows);
        return $errorRows;
    }

    /**
     * Find source file options, which have analogs in DB with the same name, but with different type
     *
     * @return array
     * @since 2.0.0
     */
    protected function _findNewOldOptionsTypeMismatch()
    {
        $errorRows = [];
        foreach ($this->_newOptionsOldData as $productId => $options) {
            foreach ($options as $outerData) {
                if (isset($this->_oldCustomOptions[$productId])) {
                    foreach ($this->_oldCustomOptions[$productId] as $innerData) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles && $outerData['type'] != $innerData['type']) {
                                $errorRows = array_merge($errorRows, $outerData['rows']);
                            }
                        }
                    }
                }
            }
        }
        sort($errorRows);
        return $errorRows;
    }

    /**
     * Checks that option exists in DB
     *
     * @param array $newOptionData
     * @param array $newOptionTitles
     * @return bool|int
     * @since 2.0.0
     */
    protected function _findExistingOptionId(array $newOptionData, array $newOptionTitles)
    {
        $productId = $newOptionData['product_id'];
        if (isset($this->_oldCustomOptions[$productId])) {
            ksort($newOptionTitles);
            $existingOptions = $this->_oldCustomOptions[$productId];
            foreach ($existingOptions as $optionId => $optionData) {
                if ($optionData['type'] == $newOptionData['type'] && $optionData['titles'] == $newOptionTitles) {
                    return $optionId;
                }
            }
        }

        return false;
    }

    /**
     * Add errors for all required rows
     *
     * @param string $errorCode
     * @param array $errorNumbers
     * @return void
     * @since 2.0.0
     */
    protected function _addRowsErrors($errorCode, array $errorNumbers)
    {
        foreach ($errorNumbers as $rowNumber) {
            $this->_productEntity->addRowError($errorCode, $rowNumber);
        }
    }

    /**
     * Validate main custom option row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    protected function _validateMainRow(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_STORE]) && !array_key_exists(
            $rowData[self::COLUMN_STORE],
            $this->_storeCodeToId
        )
        ) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
        } elseif (!empty($rowData[self::COLUMN_TYPE]) && !array_key_exists(
            $rowData[self::COLUMN_TYPE],
            $this->_specificTypes
        )
        ) {
            // type
            $this->_productEntity->addRowError(self::ERROR_INVALID_TYPE, $rowNumber);
        } elseif (empty($rowData[self::COLUMN_TITLE])) {
            // title
            $this->_productEntity->addRowError(self::ERROR_EMPTY_TITLE, $rowNumber);
        } elseif ($this->_validateSpecificTypeParameters($rowData, $rowNumber)) {
            // price, max_character
            if ($this->_validateMainRowAdditionalData($rowData, $rowNumber)) {
                $this->_saveNewOptionData($rowData, $rowNumber);
                return true;
            }
        }
        return false;
    }

    /**
     * Validation of additional data in main row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    protected function _validateMainRowAdditionalData(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_SORT_ORDER]) && !ctype_digit((string)$rowData[self::COLUMN_SORT_ORDER])) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_SORT_ORDER, $rowNumber);
        } else {
            return true;
        }
        return false;
    }

    /**
     * Save validated option data
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @since 2.0.0
     */
    protected function _saveNewOptionData(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_SKU])) {
            $this->_rowProductSku = $rowData[self::COLUMN_SKU];
        }
        if (!empty($rowData[self::COLUMN_TYPE])) {
            $this->_newCustomOptionId++;
        }
        // get store ID
        if (!empty($rowData[self::COLUMN_STORE])) {
            $storeCode = $rowData[self::COLUMN_STORE];
            $storeId = $this->_storeCodeToId[$storeCode];
        } else {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        if (isset($this->_productsSkuToId[$this->_rowProductSku])) {
            // save in existing data array
            $productId = $this->_productsSkuToId[$this->_rowProductSku];
            if (!isset($this->_newOptionsOldData[$productId])) {
                $this->_newOptionsOldData[$productId] = [];
            }
            if (!isset($this->_newOptionsOldData[$productId][$this->_newCustomOptionId])) {
                $this->_newOptionsOldData[$productId][$this->_newCustomOptionId] = [
                    'titles' => [],
                    'rows' => [],
                    'type' => $rowData[self::COLUMN_TYPE],
                ];
            }
            // set title
            $this->_newOptionsOldData[$productId][$this
                ->_newCustomOptionId]['titles'][$storeId] = $rowData[self::COLUMN_TITLE];
            // set row number
            $this->_newOptionsOldData[$productId][$this->_newCustomOptionId]['rows'][] = $rowNumber;
        } else {
            // save in new data array
            $productSku = $this->_rowProductSku;
            if (!isset($this->_newOptionsNewData[$this->_rowProductSku])) {
                $this->_newOptionsNewData[$this->_rowProductSku] = [];
            }
            if (!isset($this->_newOptionsNewData[$productSku][$this->_newCustomOptionId])) {
                $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId] = [
                    'titles' => [],
                    'rows' => [],
                    'type' => $rowData[self::COLUMN_TYPE],
                ];
            }
            // set title
            $this->_newOptionsNewData[$productSku][$this
                ->_newCustomOptionId]['titles'][$storeId] = $rowData[self::COLUMN_TITLE];
            // set row number
            $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId]['rows'][] = $rowNumber;
        }
    }

    /**
     * Validate secondary custom option row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    protected function _validateSecondaryRow(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_STORE]) && !array_key_exists(
            $rowData[self::COLUMN_STORE],
            $this->_storeCodeToId
        )
        ) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
        } elseif (!empty($rowData[self::COLUMN_ROW_PRICE]) && !is_numeric(rtrim($rowData[self::COLUMN_ROW_PRICE], '%'))
        ) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_ROW_PRICE, $rowNumber);
        } elseif (!empty($rowData[self::COLUMN_ROW_SORT]) && !ctype_digit((string)$rowData[self::COLUMN_ROW_SORT])) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_ROW_SORT, $rowNumber);
        } else {
            if (isset($this->_productsSkuToId[$this->_rowProductSku])) {
                $productId = $this->_productsSkuToId[$this->_rowProductSku];
                $this->_newOptionsOldData[$productId][$this->_newCustomOptionId]['rows'][] = $rowNumber;
            } else {
                $productSku = $this->_rowProductSku;
                $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId]['rows'][] = $rowNumber;
            }
            return true;
        }
        return false;
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            return !isset($this->_invalidRows[$rowNumber]);
        }
        $this->_validatedRows[$rowNumber] = true;

        $multiRowData = $this->_getMultiRowFormat($rowData);

        foreach ($multiRowData as $optionData) {

            $combinedData = array_merge($rowData, $optionData);

            if ($this->_isRowWithCustomOption($combinedData)) {
                if ($this->_isMainOptionRow($combinedData)) {
                    if (!$this->_validateMainRow($combinedData, $rowNumber)) {
                        return false;
                    }
                }
                if ($this->_isSecondaryOptionRow($combinedData)) {
                    if (!$this->_validateSecondaryRow($combinedData, $rowNumber)) {
                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Validation of specific type parameters
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    protected function _validateSpecificTypeParameters(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_TYPE])) {
            if (isset($this->_specificTypes[$rowData[self::COLUMN_TYPE]])) {
                $typeParameters = $this->_specificTypes[$rowData[self::COLUMN_TYPE]];
                if (is_array($typeParameters)) {
                    foreach ($typeParameters as $typeParameter) {
                        if (!$this->_validateSpecificParameterData($typeParameter, $rowData, $rowNumber)) {
                            return false;
                        }
                    }
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate one specific parameter
     *
     * @param string $typeParameter
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    protected function _validateSpecificParameterData($typeParameter, array $rowData, $rowNumber)
    {
        $fieldName = self::COLUMN_PREFIX . $typeParameter;
        if ($typeParameter == 'price') {
            if (!empty($rowData[$fieldName]) && !is_numeric(rtrim($rowData[$fieldName], '%'))) {
                $this->_productEntity->addRowError(self::ERROR_INVALID_PRICE, $rowNumber);
                return false;
            }
        } elseif ($typeParameter == 'max_characters') {
            if (!empty($rowData[$fieldName]) && !ctype_digit((string)$rowData[$fieldName])) {
                $this->_productEntity->addRowError(self::ERROR_INVALID_MAX_CHARACTERS, $rowNumber);
                return false;
            }
        }
        return true;
    }

    /**
     * Checks that current row contains custom option information
     *
     * @param array $rowData
     * @return bool
     * @since 2.0.0
     */
    protected function _isRowWithCustomOption(array $rowData)
    {
        return !empty($rowData[self::COLUMN_TYPE]) ||
            !empty($rowData[self::COLUMN_TITLE]) ||
            !empty($rowData[self::COLUMN_ROW_TITLE]);
    }

    /**
     * Checks that current row a main option row (i.e. contains option data)
     *
     * @param array $rowData
     * @return bool
     * @since 2.0.0
     */
    protected function _isMainOptionRow(array $rowData)
    {
        return !empty($rowData[self::COLUMN_TYPE]) || !empty($rowData[self::COLUMN_TITLE]);
    }

    /**
     * Checks that current row a secondary option row (i.e. contains option value data)
     *
     * @param array $rowData
     * @return bool
     * @since 2.0.0
     */
    protected function _isSecondaryOptionRow(array $rowData)
    {
        return !empty($rowData[self::COLUMN_ROW_TITLE]);
    }

    /**
     * Checks that complex options contain values
     *
     * @param array &$options
     * @param array &$titles
     * @param array $typeValues
     * @return bool
     * @since 2.0.0
     */
    protected function _isReadyForSaving(array &$options, array &$titles, array $typeValues)
    {
        // if complex options do not contain values - ignore them
        foreach ($options as $key => $optionData) {
            $optionId = $optionData['option_id'];
            $optionType = $optionData['type'];
            if ($this->_specificTypes[$optionType] === true && !isset($typeValues[$optionId])) {
                unset($options[$key], $titles[$optionId]);
            }
        }
        if ($options) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get multiRow format from one line data.
     *
     * @param array $rowData
     * @return array
     * @since 2.0.0
     */
    protected function _getMultiRowFormat($rowData)
    {
        // Parse custom options.
        $rowData = $this->_parseCustomOptions($rowData);
        $multiRow = [];
        if (empty($rowData['custom_options'])) {
            return $multiRow;
        }

        $i = 0;
        foreach ($rowData['custom_options'] as $name => $customOption) {
            $i++;
            foreach ($customOption as $rowOrder => $optionRow) {
                $row = array_merge(
                    [
                        self::COLUMN_STORE => '',
                        self::COLUMN_TITLE => $name,
                        self::COLUMN_SORT_ORDER => $i,
                        self::COLUMN_ROW_SORT => $rowOrder
                    ],
                    $this->processOptionRow($name, $optionRow)
                );
                $name = '';
                $multiRow[] = $row;
            }
        }

        return $multiRow;
    }

    /**
     * @param string $name
     * @param array $optionRow
     * @return array
     * @since 2.1.0
     */
    private function processOptionRow($name, $optionRow)
    {
        $result = [
            self::COLUMN_TYPE => $name ? $optionRow['type'] : '',
            self::COLUMN_IS_REQUIRED => $optionRow['required'],
            self::COLUMN_ROW_SKU => $optionRow['sku'],
            self::COLUMN_PREFIX . 'sku' => $optionRow['sku'],
            self::COLUMN_ROW_TITLE => '',
            self::COLUMN_ROW_PRICE => ''
        ];

        if (isset($optionRow['option_title'])) {
            $result[self::COLUMN_ROW_TITLE] = $optionRow['option_title'];
        }

        if (isset($optionRow['price'])) {
            $percent_suffix = '';
            if (isset($optionRow['price_type']) && $optionRow['price_type'] == 'percent') {
                $percent_suffix = '%';
            }
            $result[self::COLUMN_ROW_PRICE] = $optionRow['price'] . $percent_suffix;
        }

        $result[self::COLUMN_PREFIX . 'price'] = $result[self::COLUMN_ROW_PRICE];

        if (isset($optionRow['max_characters'])) {
            $result[$this->columnMaxCharacters] = $optionRow['max_characters'];
        }

        $result = $this->addFileOptions($result, $optionRow);

        return $result;
    }

    /**
     * Add file options
     *
     * @param array $result
     * @param array $optionRow
     * @return array
     * @since 2.2.0
     */
    private function addFileOptions($result, $optionRow)
    {
        foreach (['file_extension', 'image_size_x', 'image_size_y'] as $fileOptionKey) {
            if (!isset($optionRow[$fileOptionKey])) {
                continue;
            }

            $result[self::COLUMN_PREFIX . $fileOptionKey] = $optionRow[$fileOptionKey];
        }

        return $result;
    }

    /**
     * Import data rows
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _importData()
    {
        $this->_initProductsSku();

        $nextOptionId = $this->_resourceHelper->getNextAutoincrement($this->_tables['catalog_product_option']);
        $nextValueId = $this->_resourceHelper->getNextAutoincrement(
            $this->_tables['catalog_product_option_type_value']
        );
        $prevOptionId = 0;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $products = [];
            $options = [];
            $titles = [];
            $prices = [];
            $typeValues = [];
            $typePrices = [];
            $typeTitles = [];
            $parentCount = [];
            $childCount = [];

            foreach ($bunch as $rowNumber => $rowData) {

                $multiRowData = $this->_getMultiRowFormat($rowData);

                foreach ($multiRowData as $optionData) {

                    $combinedData = array_merge($rowData, $optionData);

                    if (!$this->isRowAllowedToImport($combinedData, $rowNumber)) {
                        continue;
                    }
                    if (!$this->_parseRequiredData($combinedData)) {
                        continue;
                    }
                    $optionData = $this->_collectOptionMainData(
                        $combinedData,
                        $prevOptionId,
                        $nextOptionId,
                        $products,
                        $prices
                    );
                    if ($optionData != null) {
                        $options[] = $optionData;
                    }
                    $this->_collectOptionTypeData(
                        $combinedData,
                        $prevOptionId,
                        $nextValueId,
                        $typeValues,
                        $typePrices,
                        $typeTitles,
                        $parentCount,
                        $childCount
                    );
                    $this->_collectOptionTitle($combinedData, $prevOptionId, $titles);
                }
            }

            // Save prepared custom options data !!!
            if ($this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
                $this->_deleteEntities(array_keys($products));
            }

            if ($this->_isReadyForSaving($options, $titles, $typeValues)) {
                if ($this->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
                    $this->_compareOptionsWithExisting($options, $titles, $prices, $typeValues);
                    $this->restoreOriginalOptionTypeIds($typeValues, $typePrices, $typeTitles);
                }

                $this->_saveOptions(
                    $options
                )->_saveTitles(
                    $titles
                )->_savePrices(
                    $prices
                )->_saveSpecificTypeValues(
                    $typeValues
                )->_saveSpecificTypePrices(
                    $typePrices
                )->_saveSpecificTypeTitles(
                    $typeTitles
                )->_updateProducts(
                    $products
                );
            }
        }

        return true;
    }

    /**
     * Load data of existed products
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initProductsSku()
    {
        if (!$this->_productsSkuToId || !empty($this->_newOptionsNewData)) {
            $columns = ['entity_id', 'sku'];
            if ($this->getProductEntityLinkField() != $this->getProductIdentifierField()) {
                $columns[] = $this->getProductEntityLinkField();
            }
            foreach ($this->_productModel->getProductEntitiesInfo($columns) as $product) {
                $this->_productsSkuToId[$product['sku']] = $product[$this->getProductEntityLinkField()];
            }
        }

        return $this;
    }

    /**
     * Collect custom option main data to import
     *
     * @param array $rowData
     * @param int &$prevOptionId
     * @param int &$nextOptionId
     * @param array &$products
     * @param array &$prices
     * @return array|null
     * @since 2.0.0
     */
    protected function _collectOptionMainData(
        array $rowData,
        &$prevOptionId,
        &$nextOptionId,
        array &$products,
        array &$prices
    ) {
        $optionData = null;

        if ($this->_rowIsMain) {
            $optionData = $this->_getOptionData($rowData, $this->_rowProductId, $nextOptionId, $this->_rowType);

            if (!$this->_isRowHasSpecificType(
                    $this->_rowType
                ) && ($priceData = $this->_getPriceData(
                    $rowData,
                    $nextOptionId,
                    $this->_rowType
                ))
            ) {
                $prices[$nextOptionId] = $priceData;
            }

            if (!isset($products[$this->_rowProductId])) {
                $products[$this->_rowProductId] = $this->_getProductData($rowData, $this->_rowProductId);
            }

            $prevOptionId = $nextOptionId++;
        }

        return $optionData;
    }

    /**
     * Collect custom option type data to import
     *
     * @param array $rowData
     * @param int &$prevOptionId
     * @param int &$nextValueId
     * @param array &$typeValues
     * @param array &$typePrices
     * @param array &$typeTitles
     * @param array &$parentCount
     * @param array &$childCount
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _collectOptionTypeData(
        array $rowData,
        &$prevOptionId,
        &$nextValueId,
        array &$typeValues,
        array &$typePrices,
        array &$typeTitles,
        array &$parentCount,
        array &$childCount
    ) {
        if ($this->_isRowHasSpecificType($this->_rowType) && $prevOptionId) {
            $specificTypeData = $this->_getSpecificTypeData($rowData, $nextValueId);
            //For default store
            if ($specificTypeData) {
                $typeValues[$prevOptionId][] = $specificTypeData['value'];

                // ensure default title is set
                if (!isset($typeTitles[$nextValueId][\Magento\Store\Model\Store::DEFAULT_STORE_ID])) {
                    $typeTitles[$nextValueId][\Magento\Store\Model\Store::DEFAULT_STORE_ID] = $specificTypeData['title'];
                }

                if ($specificTypeData['price']) {
                    if ($this->_isPriceGlobal) {
                        $typePrices[$nextValueId][\Magento\Store\Model\Store::DEFAULT_STORE_ID] = $specificTypeData['price'];
                    } else {
                        // ensure default price is set
                        if (!isset($typePrices[$nextValueId][\Magento\Store\Model\Store::DEFAULT_STORE_ID])) {
                            $typePrices[$nextValueId][\Magento\Store\Model\Store::DEFAULT_STORE_ID] = $specificTypeData['price'];
                        }
                        $typePrices[$nextValueId][$this->_rowStoreId] = $specificTypeData['price'];
                    }
                }

                $nextValueId++;
                if (isset($parentCount[$prevOptionId])) {
                    $parentCount[$prevOptionId]++;
                } else {
                    $parentCount[$prevOptionId] = 1;
                }
            }

            if (!isset($childCount[$this->_rowStoreId][$prevOptionId])) {
                $childCount[$this->_rowStoreId][$prevOptionId] = 0;
            }
            $parentValueId = $nextValueId - $parentCount[$prevOptionId] + $childCount[$this->_rowStoreId][$prevOptionId];
            $specificTypeData = $this->_getSpecificTypeData($rowData, $parentValueId, false);
            //For others stores
            if ($specificTypeData) {
                $typeTitles[$parentValueId][$this->_rowStoreId] = $specificTypeData['title'];
                $childCount[$this->_rowStoreId][$prevOptionId]++;
            }
        }
    }

    /**
     * Collect custom option title to import
     *
     * @param array $rowData
     * @param int $prevOptionId
     * @param array &$titles
     * @return void
     * @since 2.0.0
     */
    protected function _collectOptionTitle(array $rowData, $prevOptionId, array &$titles)
    {
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if (!empty($rowData[self::COLUMN_TITLE])) {
            if (!isset($titles[$prevOptionId][$defaultStoreId])) {
                // ensure default title is set
                $titles[$prevOptionId][$defaultStoreId] = $rowData[self::COLUMN_TITLE];
            }
            $titles[$prevOptionId][$this->_rowStoreId] = $rowData[self::COLUMN_TITLE];
        }
    }

    /**
     * Find duplicated custom options and update existing options data
     *
     * @param array &$options
     * @param array &$titles
     * @param array &$prices
     * @param array &$typeValues
     * @return $this
     * @since 2.0.0
     */
    protected function _compareOptionsWithExisting(array &$options, array &$titles, array &$prices, array &$typeValues)
    {
        foreach ($options as &$optionData) {
            $newOptionId = $optionData['option_id'];
            if ($optionId = $this->_findExistingOptionId($optionData, $titles[$newOptionId])) {
                $optionData['option_id'] = $optionId;
                $titles[$optionId] = $titles[$newOptionId];
                unset($titles[$newOptionId]);
                if (isset($prices[$newOptionId])) {
                    $prices[$newOptionId]['option_id'] = $optionId;
                }
                if (isset($typeValues[$newOptionId])) {
                    $typeValues[$optionId] = $typeValues[$newOptionId];
                    unset($typeValues[$newOptionId]);
                }
            }
        }

        return $this;
    }

    /**
     * Restore original IDs for existing option types.
     *
     * Warning: arguments are modified by reference
     *
     * @param array $typeValues
     * @param array $typePrices
     * @param array $typeTitles
     * @return void
     */
    private function restoreOriginalOptionTypeIds(array &$typeValues, array &$typePrices, array &$typeTitles)
    {
        foreach ($typeValues as $optionId => &$optionTypes) {
            foreach ($optionTypes as &$optionType) {
                $optionTypeId = $optionType['option_type_id'];
                foreach ($typeTitles[$optionTypeId] as $storeId => $optionTypeTitle) {
                    $existingTypeId = $this->getExistingOptionTypeId($optionId, $storeId, $optionTypeTitle);
                    if ($existingTypeId) {
                        $optionType['option_type_id'] = $existingTypeId;
                        $typeTitles[$existingTypeId] = $typeTitles[$optionTypeId];
                        unset($typeTitles[$optionTypeId]);
                        $typePrices[$existingTypeId] = $typePrices[$optionTypeId];
                        unset($typePrices[$optionTypeId]);
                        // If option type titles match at least in one store, consider current option type as existing
                        break;
                    }
                }
            }
        }
    }

    /**
     * Identify ID of the provided option type by its title in the specified store.
     *
     * @param int $optionId
     * @param int $storeId
     * @param string $optionTypeTitle
     * @return int|null
     */
    private function getExistingOptionTypeId($optionId, $storeId, $optionTypeTitle)
    {
        if (!isset($this->optionTypeTitles[$storeId])) {
            /** @var ProductOptionValueCollection $optionTypeCollection */
            $optionTypeCollection = $this->productOptionValueCollectionFactory->create();
            $optionTypeCollection->addTitleToResult($storeId);
            /** @var \Magento\Catalog\Model\Product\Option\Value $type */
            foreach ($optionTypeCollection as $type) {
                $this->optionTypeTitles[$storeId][$type->getOptionId()][$type->getId()] = $type->getTitle();
            }
        }
        if (isset($this->optionTypeTitles[$storeId][$optionId])
            && is_array($this->optionTypeTitles[$storeId][$optionId])
        ) {
            foreach ($this->optionTypeTitles[$storeId][$optionId] as $optionTypeId => $currentTypeTitle) {
                if ($optionTypeTitle === $currentTypeTitle) {
                    return $optionTypeId;
                }
            }
        }
        return null;
    }

    /**
     * Parse required data from current row and store to class internal variables some data
     * for underlying dependent rows
     *
     * @param array $rowData
     * @return bool
     * @since 2.0.0
     */
    protected function _parseRequiredData(array $rowData)
    {
        if ($rowData[self::COLUMN_SKU] != '' && isset($this->_productsSkuToId[$rowData[self::COLUMN_SKU]])) {
            $this->_rowProductId = $this->_productsSkuToId[$rowData[self::COLUMN_SKU]];
        } elseif (!isset($this->_rowProductId)) {
            return false;
        }

        // Init store
        if (!empty($rowData[self::COLUMN_STORE])) {
            if (!isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                return false;
            }
            $this->_rowStoreId = $this->_storeCodeToId[$rowData[self::COLUMN_STORE]];
        } else {
            $this->_rowStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        // Init option type and set param which tell that row is main
        if (!empty($rowData[self::COLUMN_TYPE])) {
            // get custom option type if its specified
            if (!isset($this->_specificTypes[$rowData[self::COLUMN_TYPE]])) {
                $this->_rowType = null;
                return false;
            }
            $this->_rowType = $rowData[self::COLUMN_TYPE];
            $this->_rowIsMain = true;
        } else {
            if (null === $this->_rowType) {
                return false;
            }
            $this->_rowIsMain = false;
        }

        return [$this->_rowProductId, $this->_rowStoreId, $this->_rowType, $this->_rowIsMain];
    }

    /**
     * Checks that current row has specific type
     *
     * @param string $type
     * @return bool
     * @since 2.0.0
     */
    protected function _isRowHasSpecificType($type)
    {
        if (isset($this->_specificTypes[$type])) {
            return $this->_specificTypes[$type] === true;
        }

        return false;
    }

    /**
     * Retrieve product data for future update
     *
     * @param array $rowData
     * @param int $productId
     * @return array
     * @since 2.0.0
     */
    protected function _getProductData(array $rowData, $productId)
    {
        $productData = [
            $this->getProductEntityLinkField() => $productId,
            'has_options' => 1,
            'required_options' => 0,
            'updated_at' => $this->dateTime->date(null, null, false)->format('Y-m-d H:i:s'),
        ];

        if (!empty($rowData[self::COLUMN_IS_REQUIRED])) {
            $productData['required_options'] = 1;
        }

        return $productData;
    }

    /**
     * Retrieve option data
     *
     * @param array $rowData
     * @param int $productId
     * @param int $optionId
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    protected function _getOptionData(array $rowData, $productId, $optionId, $type)
    {
        $optionData = [
            'option_id' => $optionId,
            'sku' => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => $productId,
            'type' => $type,
            'is_require' => empty($rowData[self::COLUMN_IS_REQUIRED]) ? 0 : 1,
            'sort_order' => empty($rowData[self::COLUMN_SORT_ORDER]) ? 0 : abs($rowData[self::COLUMN_SORT_ORDER]),
        ];

        if (!$this->_isRowHasSpecificType($type)) {
            // simple option may have optional params
            foreach ($this->_specificTypes[$type] as $paramSuffix) {
                if (isset($rowData[self::COLUMN_PREFIX . $paramSuffix])) {
                    $data = $rowData[self::COLUMN_PREFIX . $paramSuffix];

                    if (array_key_exists($paramSuffix, $optionData)) {
                        $optionData[$paramSuffix] = $data;
                    }
                }
            }
        }
        return $optionData;
    }

    /**
     * Retrieve price data or false in case when price is empty
     *
     * @param array $rowData
     * @param int $optionId
     * @param string $type
     * @return array|bool
     * @since 2.0.0
     */
    protected function _getPriceData(array $rowData, $optionId, $type)
    {
        if (in_array(
                'price',
                $this->_specificTypes[$type]
            ) && isset(
                $rowData[self::COLUMN_PREFIX . 'price']
            ) && strlen(
                $rowData[self::COLUMN_PREFIX . 'price']
            ) > 0
        ) {
            $priceData = [
                'option_id' => $optionId,
                'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                'price_type' => 'fixed',
            ];

            $data = $rowData[self::COLUMN_PREFIX . 'price'];
            if ('%' == substr($data, -1)) {
                $priceData['price_type'] = 'percent';
            }
            $priceData['price'] = (double)rtrim($data, '%');

            return $priceData;
        }

        return false;
    }

    /**
     * Retrieve specific type data
     *
     * @param array $rowData
     * @param int $optionTypeId
     * @param bool $defaultStore
     * @return array|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _getSpecificTypeData(array $rowData, $optionTypeId, $defaultStore = true)
    {
        if (!empty($rowData[self::COLUMN_ROW_TITLE]) && $defaultStore && empty($rowData[self::COLUMN_STORE])) {
            $valueData = [
                'option_type_id' => $optionTypeId,
                'sort_order' => empty($rowData[self::COLUMN_ROW_SORT]) ? 0 : abs($rowData[self::COLUMN_ROW_SORT]),
                'sku' => !empty($rowData[self::COLUMN_ROW_SKU]) ? $rowData[self::COLUMN_ROW_SKU] : '',
            ];

            $priceData = false;
            if (!empty($rowData[self::COLUMN_ROW_PRICE])) {
                $priceData = [
                    'price' => (double)rtrim($rowData[self::COLUMN_ROW_PRICE], '%'),
                    'price_type' => 'fixed',
                ];
                if ('%' == substr($rowData[self::COLUMN_ROW_PRICE], -1)) {
                    $priceData['price_type'] = 'percent';
                }
            }
            return ['value' => $valueData, 'title' => $rowData[self::COLUMN_ROW_TITLE], 'price' => $priceData];
        } elseif (!empty($rowData[self::COLUMN_ROW_TITLE]) && !$defaultStore && !empty($rowData[self::COLUMN_STORE])) {
            return ['title' => $rowData[self::COLUMN_ROW_TITLE]];
        }
        return false;
    }

    /**
     * Delete custom options for products
     *
     * @param array $productIds
     * @return $this
     * @since 2.0.0
     */
    protected function _deleteEntities(array $productIds)
    {
        $this->_connection->delete(
            $this->_tables['catalog_product_option'],
            $this->_connection->quoteInto('product_id IN (?)', $productIds)
        );

        return $this;
    }

    /**
     * Delete custom option type values
     *
     * @param array $optionIds
     * @return $this
     * @since 2.0.0
     */
    protected function _deleteSpecificTypeValues(array $optionIds)
    {
        $this->_connection->delete(
            $this->_tables['catalog_product_option_type_value'],
            $this->_connection->quoteInto('option_id IN (?)', $optionIds)
        );

        return $this;
    }

    /**
     * Save custom options main info
     *
     * @param array $options Options data
     * @return $this
     * @since 2.0.0
     */
    protected function _saveOptions(array $options)
    {
        $this->_connection->insertOnDuplicate($this->_tables['catalog_product_option'], $options);

        return $this;
    }

    /**
     * Save custom option titles
     *
     * @param array $titles Option titles data
     * @return $this
     * @since 2.0.0
     */
    protected function _saveTitles(array $titles)
    {
        $titleRows = [];
        foreach ($titles as $optionId => $storeInfo) {
            foreach ($storeInfo as $storeId => $title) {
                $titleRows[] = ['option_id' => $optionId, 'store_id' => $storeId, 'title' => $title];
            }
        }
        if ($titleRows) {
            $this->_connection->insertOnDuplicate(
                $this->_tables['catalog_product_option_title'],
                $titleRows,
                ['title']
            );
        }

        return $this;
    }

    /**
     * Save custom option prices
     *
     * @param array $prices Option prices data
     * @return $this
     * @since 2.0.0
     */
    protected function _savePrices(array $prices)
    {
        if ($prices) {
            $this->_connection->insertOnDuplicate(
                $this->_tables['catalog_product_option_price'],
                $prices,
                ['price', 'price_type']
            );
        }

        return $this;
    }

    /**
     * Save custom option type values
     *
     * @param array $typeValues Option type values
     * @return $this
     * @since 2.0.0
     */
    protected function _saveSpecificTypeValues(array $typeValues)
    {
        $this->_deleteSpecificTypeValues(array_keys($typeValues));

        $typeValueRows = [];
        foreach ($typeValues as $optionId => $optionInfo) {
            foreach ($optionInfo as $row) {
                $row['option_id'] = $optionId;
                $typeValueRows[] = $row;
            }
        }
        if ($typeValueRows) {
            $this->_connection->insertMultiple($this->_tables['catalog_product_option_type_value'], $typeValueRows);
        }

        return $this;
    }

    /**
     * Save custom option type prices
     *
     * @param array $typePrices option type prices
     * @return $this
     * @since 2.0.0
     */
    protected function _saveSpecificTypePrices(array $typePrices)
    {
        $optionTypePriceRows = [];
        foreach ($typePrices as $optionTypeId => $storesData) {
            foreach ($storesData as $storeId => $row) {
                $row['option_type_id'] = $optionTypeId;
                $row['store_id'] = $storeId;
                $optionTypePriceRows[] = $row;
            }
        }
        if ($optionTypePriceRows) {
            $this->_connection->insertOnDuplicate(
                $this->_tables['catalog_product_option_type_price'],
                $optionTypePriceRows,
                ['price', 'price_type']
            );
        }

        return $this;
    }

    /**
     * Save custom option type titles
     *
     * @param array $typeTitles Option type titles
     * @return $this
     * @since 2.0.0
     */
    protected function _saveSpecificTypeTitles(array $typeTitles)
    {
        $optionTypeTitleRows = [];
        foreach ($typeTitles as $optionTypeId => $storesData) {
            foreach ($storesData as $storeId => $title) {
                $optionTypeTitleRows[] = [
                    'option_type_id' => $optionTypeId,
                    'store_id' => $storeId,
                    'title' => $title,
                ];
            }
        }
        if ($optionTypeTitleRows) {
            $this->_connection->insertOnDuplicate(
                $this->_tables['catalog_product_option_type_title'],
                $optionTypeTitleRows,
                ['title']
            );
        }

        return $this;
    }

    /**
     * Update product data which related to custom options information
     *
     * @param array $data Product data which will be updated
     * @return $this
     * @since 2.0.0
     */
    protected function _updateProducts(array $data)
    {
        if ($data) {
            $this->_connection->insertOnDuplicate(
                $this->_tables['catalog_product_entity'],
                $data,
                ['has_options', 'required_options', 'updated_at']
            );
        }

        return $this;
    }

    /**
     * Parse custom options string to inner format.
     *
     * @param array $rowData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _parseCustomOptions($rowData)
    {
        $beforeOptionValueSkuDelimiter = ';';
        if (empty($rowData['custom_options'])) {
            return $rowData;
        }
        $rowData['custom_options'] = str_replace(
            $beforeOptionValueSkuDelimiter,
            $this->_productEntity->getMultipleValueSeparator(),
            $rowData['custom_options']
        );
        $options = [];
        $optionValues = explode(Product::PSEUDO_MULTI_LINE_SEPARATOR, $rowData['custom_options']);
        $k = 0;
        $name = '';
        foreach ($optionValues as $optionValue) {
            $optionValueParams = explode($this->_productEntity->getMultipleValueSeparator(), $optionValue);
            foreach ($optionValueParams as $nameAndValue) {
                $nameAndValue = explode('=', $nameAndValue);
                if (!empty($nameAndValue)) {
                    $value = isset($nameAndValue[1]) ? $nameAndValue[1] : '';
                    $value = trim($value);
                    $fieldName = trim($nameAndValue[0]);
                    if ($value && ($fieldName == 'name')) {
                        if ($name != $value) {
                            $name = $value;
                            $k = 0;
                        }
                    }
                    if ($name) {
                        $options[$name][$k][$fieldName] = $value;
                    }
                }
            }
            $options[$name][$k]['_custom_option_store'] = $rowData[Product::COL_STORE_VIEW_CODE];
            $k++;
        }
        $rowData['custom_options'] = $options;
        return $rowData;
    }

    /**
     * Clear product sku to id array.
     *
     * @return $this
     * @since 2.0.0
     */
    public function clearProductsSkuToId()
    {
        $this->_productsSkuToId = null;
        return $this;
    }

    /**
     * Get product entity link field
     *
     * @return string
     * @since 2.1.0
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     * @since 2.1.0
     */
    private function getProductIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }
}
