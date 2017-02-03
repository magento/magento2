<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Export;

use Magento\Store\Model\Store;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as ImportAdvancedPricing;
use Magento\Catalog\Model\Product as CatalogProduct;

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
    ];

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
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * Export process
     *
     * @return string
     */
    public function export()
    {
        //Execution time may be very long
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
                if (isset($this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP])) {
                    if ($attribute->getAttributeCode() == ImportAdvancedPricing::COL_TIER_PRICE
                        && in_array(
                            $attribute->getId(),
                            $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP]
                        )
                    ) {
                        $this->_passTierPrice = 1;
                    }
                }
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
        $exportData = [];
        try {
            $rawData = $this->collectRawData();
            $productIds = array_keys($rawData);
            if (isset($productIds)) {
                if (!$this->_passTierPrice) {
                    $exportData = array_merge(
                        $exportData,
                        $this->getTierPrices($productIds, ImportAdvancedPricing::TABLE_TIER_PRICE)
                    );
                }
            }
            if ($exportData) {
                $exportData = $this->correctExportData($exportData);
            }
            if (isset($exportData)) {
                asort($exportData);
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }

    /**
     * @param array $exportData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
     * Get Tier and Group Pricing
     *
     * @param array $listSku
     * @param string $table
     * @return array|bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                $select = $this->_connection->select()
                    ->from(
                        ['cpe' => $this->_resource->getTableName('catalog_product_entity')],
                        $selectFields
                    )
                    ->joinInner(
                        ['ap' => $this->_resource->getTableName($table)],
                        'ap.entity_id = cpe.entity_id',
                        []
                    )
                    ->where('cpe.entity_id IN (?)', $listSku);

                if (isset($price[0]) && !empty($price[0])) {
                    $select->where('ap.value >= ?', $price[0]);
                }
                if (isset($price[1]) && !empty($price[1])) {
                    $select->where('ap.value <= ?', $price[1]);
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
     * Get Website code
     *
     * @param int $websiteId
     * @return string
     */
    protected function _getWebsiteCode($websiteId)
    {
        $storeName = ($websiteId == 0)
            ? ImportAdvancedPricing::VALUE_ALL_WEBSITES
            : $this->_storeManager->getWebsite($websiteId)->getName();
        if ($websiteId == 0) {
            $currencyCode = $this->_storeManager->getWebsite($websiteId)->getBaseCurrencyCode();
        }
        if ($storeName && $currencyCode) {
            return $storeName . ' [' . $currencyCode . ']';
        } else {
            return $storeName;
        }
    }

    /**
     * Get Customer Group By Id
     *
     * @param int $customerGroupId
     * @param null $allGroups
     * @return string
     */
    protected function _getCustomerGroupById($customerGroupId, $allGroups = null)
    {
        if ($allGroups) {
            return ImportAdvancedPricing::VALUE_ALL_GROUPS;
        } else {
            return $this->_groupRepository->getById($customerGroupId)->getCode();
        }
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
