<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Export;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as ImportAdvancedPricing;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ImportExport\Model\Export;
use Magento\Store\Model\Store;

/**
 * Export Advanced Pricing
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricing extends \Magento\CatalogImportExport\Model\Export\Product
{
    const ENTITY_ADVANCED_PRICING = 'advanced_pricing';

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $_storeResolver;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var string
     */
    protected $_entityTypeCode;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var int
     */
    protected $_passTierPrice = 0;

    /**
     * List of items websites
     *
     * @var array
     */
    protected $_priceWebsite = [
        ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE,
    ];

    /**
     * List of items customer groups
     *
     * @var array
     */
    protected $_priceCustomerGroup = [
        ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP,
    ];

    /**
     * Export template
     *
     * @var array
     */
    protected $templateExportData = [
        ImportAdvancedPricing::COL_SKU => '',
        ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE => '',
        ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => '',
        ImportAdvancedPricing::COL_TIER_PRICE_QTY => '',
        ImportAdvancedPricing::COL_TIER_PRICE => '',
        ImportAdvancedPricing::COL_TIER_PRICE_TYPE => ''
    ];

    /**
     * @var string[]
     */
    private $websiteCodesMap = [];

    /**
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
     * @param \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory
     * @param \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer
     * @param ImportProduct\StoreResolver $storeResolver
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->_storeResolver = $storeResolver;
        $this->_groupRepository = $groupRepository;
        $this->_resource = $resource;
        parent::__construct(
            $localeDate,
            $config,
            $resource,
            $storeManager,
            $logger,
            $collectionFactory,
            $exportConfig,
            $productFactory,
            $attrSetColFactory,
            $categoryColFactory,
            $itemFactory,
            $optionColFactory,
            $attributeColFactory,
            $_typeFactory,
            $linkTypeProvider,
            $rowCustomizer
        );
    }

    /**
     * Init type models
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initTypeModels()
    {
        $productTypes = $this->_exportConfig->getEntityTypes(CatalogProduct::ENTITY);
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
                __('There are no product types available for export')
            );
        }
        $this->_disabledAttrs = array_unique($this->_disabledAttrs);
        return $this;
    }

    /**
     * Export process
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $entityCollection->setOrder('has_options', 'asc');
            $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);
            $this->_prepareEntityCollection($entityCollection);
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->count() == 0) {
                break;
            }
            $entityCollection->clear();
            $exportData = $this->getExportData();
            foreach ($exportData as $dataRow) {
                $writer->writeRow($dataRow);
            }
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }
        return $writer->getContents();
    }

    /**
     * Clean up attribute collection.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function filterAttributeCollection(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection)
    {
        $collection->load();

        foreach ($collection as $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->_disabledAttrs)) {
                $collection->removeItemByKey($attribute->getId());
            }
        }
        return $collection;
    }

    /**
     * Get export data for collection
     *
     * @return array|mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getExportData()
    {
        if ($this->_passTierPrice) {
            return [];
        }

        $exportData = [];
        try {
            $productsByStores = $this->loadCollection();
            if (!empty($productsByStores)) {
                $linkField = $this->getProductEntityLinkField();
                $productLinkIds = [];

                foreach ($productsByStores as $product) {
                    $productLinkIds[array_pop($product)[$linkField]] = true;
                }
                $productLinkIds = array_keys($productLinkIds);
                $tierPricesData = $this->fetchTierPrices($productLinkIds);
                $exportData = $this->prepareExportData(
                    $productsByStores,
                    $tierPricesData
                );
                if (!empty($exportData)) {
                    asort($exportData);
                }
            }
        } catch (\Throwable $e) {
            $this->_logger->critical($e);
        }

        return $exportData;
    }

    /**
     * Creating export-formatted row from tier price.
     *
     * @param array $tierPriceData Tier price information.
     *
     * @return array Formatted for export tier price information.
     */
    private function createExportRow(array $tierPriceData): array
    {
        //List of columns to display in export row.
        $exportRow = $this->templateExportData;

        foreach (array_keys($exportRow) as $keyTemplate) {
            if (array_key_exists($keyTemplate, $tierPriceData)) {
                if (in_array($keyTemplate, $this->_priceWebsite)) {
                    //If it's website column then getting website code.
                    $exportRow[$keyTemplate] = $this->_getWebsiteCode(
                        $tierPriceData[$keyTemplate]
                    );
                } elseif (in_array($keyTemplate, $this->_priceCustomerGroup)) {
                    //If it's customer group column then getting customer
                    //group name by ID.
                    $exportRow[$keyTemplate] = $this->_getCustomerGroupById(
                        $tierPriceData[$keyTemplate],
                        $tierPriceData[ImportAdvancedPricing::VALUE_ALL_GROUPS]
                    );
                    unset($exportRow[ImportAdvancedPricing::VALUE_ALL_GROUPS]);
                } elseif ($keyTemplate
                    === ImportAdvancedPricing::COL_TIER_PRICE
                ) {
                    //If it's price column then getting value and type
                    //of tier price.
                    $exportRow[$keyTemplate]
                        = $tierPriceData[ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE]
                        ? $tierPriceData[ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE]
                        : $tierPriceData[ImportAdvancedPricing::COL_TIER_PRICE];
                    $exportRow[ImportAdvancedPricing::COL_TIER_PRICE_TYPE]
                        = $this->tierPriceTypeValue($tierPriceData);
                } else {
                    //Any other column just goes as is.
                    $exportRow[$keyTemplate] = $tierPriceData[$keyTemplate];
                }
            }
        }

        return $exportRow;
    }

    /**
     * Prepare data for export.
     *
     * @param array $productsData Products to export.
     * @param array $tierPricesData Their tier prices.
     *
     * @return array Export rows to display.
     */
    private function prepareExportData(
        array $productsData,
        array $tierPricesData
    ): array {
        //Assigning SKUs to tier prices data.
        $productLinkIdToSkuMap = [];
        foreach ($productsData as $productData) {
            $productLinkIdToSkuMap[$productData[Store::DEFAULT_STORE_ID][$this->getProductEntityLinkField()]]
                = $productData[Store::DEFAULT_STORE_ID]['sku'];
        }

        //Adding products' SKUs to tier price data.
        $linkedTierPricesData = [];
        foreach ($tierPricesData as $tierPriceData) {
            $sku = $productLinkIdToSkuMap[$tierPriceData['product_link_id']];
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $linkedTierPricesData[] = array_merge(
                $tierPriceData,
                [ImportAdvancedPricing::COL_SKU => $sku]
            );
        }

        //Formatting data for export.
        $customExportData = [];
        foreach ($linkedTierPricesData as $row) {
            $customExportData[] = $this->createExportRow($row);
        }

        return $customExportData;
    }

    /**
     * Correct export data.
     *
     * @param array $exportData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @deprecated 100.3.0
     * @see prepareExportData
     */
    protected function correctExportData($exportData)
    {
        $customExportData = [];
        foreach ($exportData as $key => $row) {
            $exportRow = $this->templateExportData;
            foreach ($exportRow as $keyTemplate => $valueTemplate) {
                if (isset($row[$keyTemplate])) {
                    if (in_array($keyTemplate, $this->_priceWebsite)) {
                        $exportRow[$keyTemplate] = $this->_getWebsiteCode(
                            $row[$keyTemplate]
                        );
                    } elseif (in_array($keyTemplate, $this->_priceCustomerGroup)) {
                        $exportRow[$keyTemplate] = $this->_getCustomerGroupById(
                            $row[$keyTemplate],
                            isset($row[ImportAdvancedPricing::VALUE_ALL_GROUPS])
                            ? $row[ImportAdvancedPricing::VALUE_ALL_GROUPS]
                            : null
                        );
                        unset($exportRow[ImportAdvancedPricing::VALUE_ALL_GROUPS]);
                    } elseif ($keyTemplate === ImportAdvancedPricing::COL_TIER_PRICE) {
                        $exportRow[$keyTemplate] = $row[ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE]
                            ? $row[ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE]
                            : $row[ImportAdvancedPricing::COL_TIER_PRICE];
                        $exportRow[ImportAdvancedPricing::COL_TIER_PRICE_TYPE]
                            = $this->tierPriceTypeValue($row[ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE]);
                    } else {
                        $exportRow[$keyTemplate] = $row[$keyTemplate];
                    }
                }
            }

            $customExportData[$key] = $exportRow;
            unset($exportRow);
        }

        return $customExportData;
    }

    /**
     * Check type for tier price.
     *
     * @param array $tierPriceData
     * @return string
     */
    private function tierPriceTypeValue(array $tierPriceData): string
    {
        return $tierPriceData[ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE]
            ? ImportAdvancedPricing::TIER_PRICE_TYPE_PERCENT
            : ImportAdvancedPricing::TIER_PRICE_TYPE_FIXED;
    }

    /**
     * Load tier prices for given products.
     *
     * @param string[] $productIds Link IDs of products to find tier prices for.
     *
     * @return array Tier prices data.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function fetchTierPrices(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException(
                'Can only load tier prices for specific products'
            );
        }

        $pricesTable = ImportAdvancedPricing::TABLE_TIER_PRICE;
        $exportFilter = null;
        $priceFromFilter = null;
        $priceToFilter = null;
        if (isset($this->_parameters[Export::FILTER_ELEMENT_GROUP])) {
            $exportFilter = $this->_parameters[Export::FILTER_ELEMENT_GROUP];
        }
        $productEntityLinkField = $this->getProductEntityLinkField();
        $selectFields = [
            ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE => 'ap.website_id',
            ImportAdvancedPricing::VALUE_ALL_GROUPS => 'ap.all_groups',
            ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'ap.customer_group_id',
            ImportAdvancedPricing::COL_TIER_PRICE_QTY => 'ap.qty',
            ImportAdvancedPricing::COL_TIER_PRICE => 'ap.value',
            ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE => 'ap.percentage_value',
            'product_link_id' => 'ap.' . $productEntityLinkField,
        ];
        if ($exportFilter && array_key_exists('tier_price', $exportFilter)) {
            if (!empty($exportFilter['tier_price'][0])) {
                $priceFromFilter = $exportFilter['tier_price'][0];
            }
            if (!empty($exportFilter['tier_price'][1])) {
                $priceToFilter = $exportFilter['tier_price'][1];
            }
        }

        $select = $this->_connection->select()
            ->from(
                ['ap' => $this->_resource->getTableName($pricesTable)],
                $selectFields
            )
            ->where(
                'ap.' . $productEntityLinkField . ' IN (?)',
                $productIds
            );

        if ($priceFromFilter !== null) {
            $select->where('ap.value >= ?', $priceFromFilter);
        }
        if ($priceToFilter !== null) {
            $select->where('ap.value <= ?', $priceToFilter);
        }
        if ($priceFromFilter || $priceToFilter) {
            $select->orWhere('ap.percentage_value IS NOT NULL');
        }

        return $this->_connection->fetchAll($select);
    }

    /**
     * Get tier prices.
     *
     * @param array $listSku
     * @param string $table
     * @return array|bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @deprecated 100.3.0
     * @see fetchTierPrices
     */
    protected function getTierPrices(array $listSku, $table)
    {
        if (isset($this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP])) {
            $exportFilter = $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP];
        }
        if ($table == ImportAdvancedPricing::TABLE_TIER_PRICE) {
            $selectFields = [
                ImportAdvancedPricing::COL_SKU => 'cpe.sku',
                ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE => 'ap.website_id',
                ImportAdvancedPricing::VALUE_ALL_GROUPS => 'ap.all_groups',
                ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'ap.customer_group_id',
                ImportAdvancedPricing::COL_TIER_PRICE_QTY => 'ap.qty',
                ImportAdvancedPricing::COL_TIER_PRICE => 'ap.value',
                ImportAdvancedPricing::COL_TIER_PRICE_PERCENTAGE_VALUE => 'ap.percentage_value',
            ];
            if (isset($exportFilter) && !empty($exportFilter)) {
                $price = $exportFilter['tier_price'];
            }
        }
        if ($listSku) {
            if (isset($exportFilter) && !empty($exportFilter)) {
                $date = $exportFilter[\Magento\Catalog\Model\Category::KEY_UPDATED_AT];
                if (isset($date[0]) && !empty($date[0])) {
                    $updatedAtFrom = $this->_localeDate->date($date[0], null, false)->format('Y-m-d H:i:s');
                }
                if (isset($date[1]) && !empty($date[1])) {
                    $updatedAtTo = $this->_localeDate->date($date[1], null, false)->format('Y-m-d H:i:s');
                }
            }
            try {
                $productEntityLinkField = $this->getProductEntityLinkField();
                $select = $this->_connection->select()
                    ->from(
                        ['cpe' => $this->_resource->getTableName('catalog_product_entity')],
                        $selectFields
                    )
                    ->joinInner(
                        ['ap' => $this->_resource->getTableName($table)],
                        'ap.' . $productEntityLinkField . ' = cpe.' . $productEntityLinkField,
                        []
                    )
                    ->where('cpe.entity_id IN (?)', $listSku);

                if (isset($price[0]) && !empty($price[0])) {
                    $select->where('ap.value >= ?', $price[0]);
                }
                if (isset($price[1]) && !empty($price[1])) {
                    $select->where('ap.value <= ?', $price[1]);
                }
                if (isset($price[0]) && !empty($price[0]) || isset($price[1]) && !empty($price[1])) {
                    $select->orWhere('ap.percentage_value IS NOT NULL');
                }
                if (isset($updatedAtFrom) && !empty($updatedAtFrom)) {
                    $select->where('cpe.updated_at >= ?', $updatedAtFrom);
                }
                if (isset($updatedAtTo) && !empty($updatedAtTo)) {
                    $select->where('cpe.updated_at <= ?', $updatedAtTo);
                }
                $exportData = $this->_connection->fetchAll($select);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $exportData;
    }

    /**
     * Get Website code.
     *
     * @param int $websiteId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getWebsiteCode(int $websiteId): string
    {
        if (!array_key_exists($websiteId, $this->websiteCodesMap)) {
            $storeName = ($websiteId == 0)
                ? ImportAdvancedPricing::VALUE_ALL_WEBSITES
                : $this->_storeManager->getWebsite($websiteId)->getCode();
            $currencyCode = '';
            if ($websiteId == 0) {
                $currencyCode = $this->_storeManager->getWebsite($websiteId)
                    ->getBaseCurrencyCode();
            }

            if ($storeName && $currencyCode) {
                $code = $storeName . ' [' . $currencyCode . ']';
            } else {
                $code = $storeName;
            }
            $this->websiteCodesMap[$websiteId] = $code;
        }

        return $this->websiteCodesMap[$websiteId];
    }

    /**
     * Get Customer Group By Id.
     *
     * @param int $groupId
     * @param int $allGroups
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getCustomerGroupById(
        int $groupId,
        int $allGroups = 0
    ): string {
        if ($allGroups !== 0) {
            return ImportAdvancedPricing::VALUE_ALL_GROUPS;
        }
        return $this->_groupRepository->getById($groupId)->getCode();
    }

    /**
     * Get Entity type code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        if (!$this->_entityTypeCode) {
            $this->_entityTypeCode = CatalogProduct::ENTITY;
        } else {
            $this->_entityTypeCode = self::ENTITY_ADVANCED_PRICING;
        }
        return $this->_entityTypeCode;
    }
}
