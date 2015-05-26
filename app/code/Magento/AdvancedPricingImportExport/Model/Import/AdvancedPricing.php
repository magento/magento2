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

    /** @var \Magento\Catalog\Model\Indexer\Product\Price\Processor */
    protected $_productPriceIndexerProcessor;

    protected $_validator;

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
        $sku = null;

        if (isset($this->_validatedRows[$rowNum])) {
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            //todo
//            if (false) {
//                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
//                return false;
//            }
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
            $this->_deleteAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->_replaceAdvancedPricing();
        }
        $this->_saveAdvancedPricing();

        return true;
    }

    protected function _saveAdvancedPricing()
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
                        'customer_group_id' => $this->_getCustomerGroupId($rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE],
                        'website_id' => $this->_getWebsiteId($rowData[self::COL_TIER_PRICE_WEBSITE])

                    ];
                }
                if (!empty($rowData[self::COL_GROUP_PRICE_WEBSITE])) {
                    $groupPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_GROUP_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL,
                        'customer_group_id' => $this->_getCustomerGroupId($rowData[self::COL_GROUP_PRICE_CUSTOMER_GROUP]),
                        'value' => $rowData[self::COL_GROUP_PRICE],
                        'website_id' => $this->_getWebSiteId($rowData[self::COL_GROUP_PRICE_WEBSITE])
                    ];
                }
            }
            $this->_saveProductTierPrices($tierPrices)
                ->_saveProductGroupPrices($groupPrices);
        }
    }

    // todo
    protected function _deleteAdvancedPricing()
    {
        return true;
    }

    // todo
    protected function _replaceAdvancedPricing()
    {
        return true;
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
        $affectedIds = [];

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getTable('catalog_product_entity_tier_price');
        }
        if ($tierPriceData) {
            $tierPriceIn = [];

            foreach ($tierPriceData as $delSku => $tierPriceRows) {
                $productId = $this->_productModel->getIdBySku($delSku);
                $affectedIds[] = $productId;

                foreach ($tierPriceRows as $row) {
                    $row['entity_id'] = $productId;
                    $tierPriceIn[] = $row;
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $affectedIds)
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
        $affectedIds = [];

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getTable('catalog_product_entity_group_price');
        }
        if ($groupPriceData) {
            $groupPriceIn = [];

            foreach ($groupPriceData as $delSku => $groupPriceRows) {
                $productId = $this->_productModel->getIdBySku($delSku);
                $affectedIds[] = $productId;

                foreach ($groupPriceRows as $row) {
                    $row['entity_id'] = $productId;
                    $groupPriceIn[] = $row;
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $affectedIds)
                );
            }
            if ($groupPriceIn) {
                $this->_connection->insertOnDuplicate($tableName, $groupPriceIn, ['value']);
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
    protected function _getWebSiteId($websiteCode)
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
    protected function _getCustomerGroupId($customerGroup)
    {
        return $customerGroup == self::VALUE_ALL ? 0 : $customerGroup;
    }
}