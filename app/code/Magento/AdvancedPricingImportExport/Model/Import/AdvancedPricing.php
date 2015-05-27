<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;

class AdvancedPricing extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const VALUE_ALL = 'all';

    const COL_SKU = 'sku';

    const COL_TIER_PRICE_WEBSITE = 'tier_price_website';

    const COL_TIER_PRICE_CUSTOMER_GROUP = 'tier_price_customer_group';

    const COL_TIER_PRICE_QTY = 'tier_price_qty';

    const COL_TIER_PRICE = 'tier_price';

    const COL_GROUP_PRICE_WEBSITE = 'group_price_website';

    const COL_GROUP_PRICE_CUSTOMER_GROUP = 'group_price_customer_group';

    const COL_GROUP_PRICE = 'group_price';

    const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';

    const TABLE_GROUPED_PRICE = 'catalog_product_entity_group_price';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)',
        ValidatorInterface::ERROR_SKU_IS_EMPTY => 'SKU is empty',
        ValidatorInterface::ERROR_NO_DEFAULT_ROW => 'Default values row does not exists',
        ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group ID is invalid',
        ValidatorInterface::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete',
        ValidatorInterface::ERROR_INVALID_GROUP_PRICE_SITE => 'Group Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_GROUP_PRICE_GROUP => 'Group Price customer group ID is invalid',
        ValidatorInterface::ERROR_GROUP_PRICE_DATA_INCOMPLETE => 'Group Price data is incomplete'
    ];

    /** @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory */
    protected $_resourceFactory;

    /** @var \Magento\Catalog\Helper\Data */
    protected $_catalogData;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productModel;

    /** @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver */
    protected $_storeResolver;

    /** @var ImportProduct */
    protected $_importProduct;

    protected $_validator;

    protected $_cachedSkuToDelete;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\ImportExport\Model\Resource\Import\Data $importData,
        \Magento\Framework\App\Resource $resource,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory $resourceFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        ImportProduct $importProduct,
        AdvancedPricing\Validator $validator
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection('write');
        $this->_resourceFactory = $resourceFactory;
        $this->_productModel = $productModel;
        $this->_catalogData = $catalogData;
        $this->_storeResolver = $storeResolver;
        $this->_importProduct = $importProduct;
        $this->_validator = $validator;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'advanced_pricing';
    }

    // todo
    public function validateRow(array $rowData, $rowNum)
    {
        $sku = false;
        if (isset($this->_validatedRows[$rowNum])) {
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (false) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }
        if (!$this->_validator->isValid($rowData)) {
            foreach ($this->_validator->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }
        if (isset($rowData[self::COL_SKU])) {
            $sku = $rowData[self::COL_SKU];
        }
        if (null === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
        } elseif (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        }
        return !isset($this->_invalidRows[$rowNum]);
    }

    /**
     * Create Advanced price data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveAdvancedPricing();
        }

        return true;
    }

    /**
     * Save advanced pricing
     */
    public function saveAdvancedPricing()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $tierPrices = [];
            $groupPrices = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $rowSku = $rowData[self::COL_SKU];
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL,
                        'customer_group_id' => $this->getCustomerGroupId($rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE],
                        'website_id' => $this->getWebsiteId($rowData[self::COL_TIER_PRICE_WEBSITE])

                    ];
                }
                if (!empty($rowData[self::COL_GROUP_PRICE_WEBSITE])) {
                    $groupPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_GROUP_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL,
                        'customer_group_id' => $this->getCustomerGroupId($rowData[self::COL_GROUP_PRICE_CUSTOMER_GROUP]),
                        'value' => $rowData[self::COL_GROUP_PRICE],
                        'website_id' => $this->getWebSiteId($rowData[self::COL_GROUP_PRICE_WEBSITE])
                    ];
                }
            }
            $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE)
                ->saveProductPrices($groupPrices, self::TABLE_GROUPED_PRICE);
        }
    }

    /**
     * Deletes Advanced price data from raw data.
     */
    protected function deleteAdvancedPricing()
    {
        $this->_cachedSkuToDelete = null;
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                } else {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    return false;
                }
                $rowSku = $rowData[self::COL_SKU];
                $listSku[] = $rowSku;
            }
        }
        if ($listSku) {
            $this->deleteProductTierAndGroupPrices(array_unique($listSku), self::TABLE_GROUPED_PRICE)
                ->deleteProductTierAndGroupPrices(array_unique($listSku), self::TABLE_TIER_PRICE);
        }
    }

    // todo
    protected function replaceAdvancedPricing()
    {
        return true;
    }

    /**
     * Save product prices.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveProductPrices(array $priceData, $table)
    {
        if ($priceData) {
            $affectedIds = [];
            $tableName = $this->_resourceFactory->create()->getTable($table);
            $priceIn = [];
            foreach ($priceData as $sku => $priceRows) {
                $productId = $this->_productModel->getIdBySku($sku);
                $affectedIds[] = $productId;
                foreach ($priceRows as $row) {
                    $row['entity_id'] = $productId;
                    $priceIn[] = $row;
                }
            }
            if ($priceIn) {
                $this->_connection->insertOnDuplicate($tableName, $priceIn, ['value']);
            }
        }
        return $this;
    }

    /**
     * Deletes tier prices and group prices.
     *
     * @param array $listSku
     * @param string $tableName
     * @return $this
     */
    protected function deleteProductTierAndGroupPrices(array $listSku, $tableName)
    {
        if ($tableName) {
            if ($listSku) {
                if(!$this->_cachedSkuToDelete) {
                    $this->_cachedSkuToDelete = $this->_connection->fetchCol($this->_connection->select()
                        ->from($this->_connection->getTableName('catalog_product_entity'), 'entity_id')
                        ->where('sku IN (?)', $listSku));
                }
                if($this->_cachedSkuToDelete) {
                    $this->_connection->delete(
                        $tableName,
                        $this->_connection->quoteInto('entity_id IN (?)', $this->_cachedSkuToDelete)
                    );
                } else {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, 0);
                    return false;
                }
            }
        }
        return $this;
    }

    /**
     * Get website id by code
     *
     * @param $websiteCode
     * @return array|int|string
     */
    protected function getWebSiteId($websiteCode)
    {
        $result = $websiteCode == self::VALUE_ALL ||
        $this->_catalogData->isPriceGlobal() ? 0 : $this->_storeResolver->getWebsiteCodeToId($websiteCode);
        return $result;
    }

    /**
     * Get customer group id
     *
     * @param string $customerGroup
     * @return int
     */
    protected function getCustomerGroupId($customerGroup)
    {
        return $customerGroup == self::VALUE_ALL ? 0 : $customerGroup;
    }
}