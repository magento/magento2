<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFactory
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory
     * @param \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\Resource\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
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
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Catalog\Model\Resource\ProductFactory $productFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory,
        \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory $itemFactory,
        \Magento\Catalog\Model\Resource\Product\Option\CollectionFactory $optionColFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->_storeResolver = $storeResolver;
        $this->_groupRepository = $groupRepository;
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
            $tierAndGroupPrices = $this->getTierAndGroupPrices($productIds);
            $exportData = $this->customValues($tierAndGroupPrices);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }

    /**
     * @param $exportData
     * @return mixed
     */
    protected function customValues(&$exportData)
    {
        if ($exportData) {
            foreach ($exportData as $key => $row) {
                if (isset($exportData[$key][ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE])) {
                    $exportData[$key][ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE] =
                        $this->_getWebsiteCode($exportData[$key][ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE]);
                }
                if (isset($exportData[$key][ImportAdvancedPricing::COL_GROUP_PRICE_WEBSITE])) {
                    $exportData[$key][ImportAdvancedPricing::COL_GROUP_PRICE_WEBSITE] =
                        $this->_getWebsiteCode($exportData[$key][ImportAdvancedPricing::COL_GROUP_PRICE_WEBSITE]);
                }
                if (isset($exportData[$key][ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP])) {
                    $exportData[$key][ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP] =
                        $this->_getCustomerGroupById((int)$exportData[$key][ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP]);
                }
                if (isset($exportData[$key][ImportAdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP])) {
                    $exportData[$key][ImportAdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP] =
                        $this->_getCustomerGroupById((int)$exportData[$key][ImportAdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP]);
                }
            }
        }
        return $exportData;
    }

    /**
     * @param array $listSku
     * @return array|bool
     */
    protected function getTierAndGroupPrices(array $listSku)
    {
        if ($listSku) {
            try {
                $exportData = $this->_connection->fetchAll($this->_connection->select()
                    ->from(['cpe' => $this->_connection->getTableName('catalog_product_entity')],
                        [
                            ImportAdvancedPricing::COL_SKU => 'cpe.sku',
                            ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE => 'cpetp.website_id',
                            ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'cpetp.customer_group_id',
                            ImportAdvancedPricing::COL_TIER_PRICE_QTY => 'cpetp.qty',
                            ImportAdvancedPricing::COL_TIER_PRICE => 'cpetp.value',
                            ImportAdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'cpegp.website_id',
                            ImportAdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'cpegp.customer_group_id',
                            ImportAdvancedPricing::COL_GROUP_PRICE => 'cpegp.value'
                        ]
                    )
                    ->joinInner(
                        ['cpegp' => $this->_connection->getTableName(ImportAdvancedPricing::TABLE_GROUPED_PRICE)],
                        'cpegp.entity_id = cpe.entity_id',
                        []
                    )
                    ->joinInner(
                        ['cpetp' => $this->_connection->getTableName(ImportAdvancedPricing::TABLE_TIER_PRICE)],
                        'cpetp.entity_id = cpe.entity_id',
                        []
                    )
                    ->where('cpe.entity_id IN (?)', $listSku));
            } catch (\Exception $e) {
                return false;
            }
        }
        return $exportData;
    }

    /**
     * @param $websiteId
     * @return string
     */
    protected function _getWebsiteCode($websiteId)
    {
        $storeName = ($websiteId == 0)
            ? ImportAdvancedPricing::VALUE_ALL_WEBSITES
            : $this->_storeManager->getWebsite($websiteId)->getName();
        $currencyCode = $this->_storeManager->getWebsite($websiteId)->getBaseCurrencyCode();
        if ($storeName && $currencyCode && ($websiteId == 0)) {
            return $storeName . ' [' . $currencyCode . ']';
        } else {
            return $storeName;
        }
    }

    /**
     * @param $customerGroupId
     * @return string
     */
    protected function _getCustomerGroupById($customerGroupId)
    {
        return $this->_groupRepository->getById($customerGroupId)->getCode();
    }

    /**
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
