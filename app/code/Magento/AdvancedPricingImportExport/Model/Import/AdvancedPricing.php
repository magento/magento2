<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class AdvancedPricing
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AdvancedPricing extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const VALUE_ALL_GROUPS = 'ALL GROUPS';

    const VALUE_ALL_WEBSITES = 'All Websites';

    const COL_SKU = 'sku';

    const COL_TIER_PRICE_WEBSITE = 'tier_price_website';

    const COL_TIER_PRICE_CUSTOMER_GROUP = 'tier_price_customer_group';

    const COL_TIER_PRICE_QTY = 'tier_price_qty';

    const COL_TIER_PRICE = 'tier_price';

    const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';

    const DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE = '0';

    const ENTITY_TYPE_CODE = 'advanced_pricing';

    const VALIDATOR_MAIN = 'validator';

    const VALIDATOR_WEBSITE = 'validator_website';

    const VALIDATOR_TEAR_PRICE = 'validator_tear_price';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)',
        ValidatorInterface::ERROR_SKU_IS_EMPTY => 'SKU is empty',
        ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group is invalid',
        ValidatorInterface::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete',
        ValidatorInterface::ERROR_INVALID_ATTRIBUTE_DECIMAL =>
            'Value for \'%s\' attribute contains incorrect value, acceptable values are in decimal format',
    ];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::COL_SKU,
        self::COL_TIER_PRICE_WEBSITE,
        self::COL_TIER_PRICE_CUSTOMER_GROUP,
        self::COL_TIER_PRICE_QTY,
        self::COL_TIER_PRICE,
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $_storeResolver;

    /**
     * @var ImportProduct
     */
    protected $_importProduct;

    /**
     * @var array
     */
    protected $_validators = [];

    /**
     * @var array
     */
    protected $_cachedSkuToDelete;

    /**
     * @var array
     */
    protected $_oldSkus = null;

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * Catalog product entity
     *
     * @var string
     */
    protected $_catalogProductEntity;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param ImportProduct\StoreResolver $storeResolver
     * @param ImportProduct $importProduct
     * @param AdvancedPricing\Validator $validator
     * @param AdvancedPricing\Validator\Website $websiteValidator
     * @param AdvancedPricing\Validator\TierPrice $tierPriceValidator
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
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        ImportProduct $importProduct,
        AdvancedPricing\Validator $validator,
        AdvancedPricing\Validator\Website $websiteValidator,
        AdvancedPricing\Validator\TierPrice $tierPriceValidator
    ) {
        $this->dateTime = $dateTime;
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
        $this->_validators[self::VALIDATOR_MAIN] = $validator->init($this);
        $this->_catalogProductEntity = $this->_resourceFactory->create()->getTable('catalog_product_entity');
        $this->_oldSkus = $this->retrieveOldSkus();
        $this->_validators[self::VALIDATOR_WEBSITE] = $websiteValidator;
        $this->_validators[self::VALIDATOR_TEAR_PRICE] = $tierPriceValidator;
        $this->errorAggregator = $errorAggregator;

        foreach (array_merge($this->errorMessageTemplates, $this->_messageTemplates) as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
    }

    /**
     * Validator object getter.
     *
     * @param string $type
     * @return AdvancedPricing\Validator|AdvancedPricing\Validator\Website
     */
    protected function _getValidator($type)
    {
        return $this->_validators[$type];
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

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $sku = false;
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::COL_SKU])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                return false;
            }
            return true;
        }
        if (!$this->_getValidator(self::VALIDATOR_MAIN)->isValid($rowData)) {
            foreach ($this->_getValidator(self::VALIDATOR_MAIN)->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }
        if (isset($rowData[self::COL_SKU])) {
            $sku = $rowData[self::COL_SKU];
        }
        if (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
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
     *
     * @return $this
     */
    public function saveAdvancedPricing()
    {
        $this->saveAndReplaceAdvancedPrices();
        return $this;
    }

    /**
     * Deletes Advanced price data from raw data.
     *
     * @return $this
     */
    public function deleteAdvancedPricing()
    {
        $this->_cachedSkuToDelete = null;
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowSku = $rowData[self::COL_SKU];
                    $listSku[] = $rowSku;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listSku) {
            $this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE);
            $this->setUpdatedAt($listSku);
        }
        return $this;
    }

    /**
     * Replace advanced pricing
     *
     * @return $this
     */
    public function replaceAdvancedPricing()
    {
        $this->saveAndReplaceAdvancedPrices();
        return $this;
    }

    /**
     * Save and replace advanced prices
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceAdvancedPrices()
    {
        $behavior = $this->getBehavior();
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cachedSkuToDelete = null;
        }
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $tierPrices = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowSku = $rowData[self::COL_SKU];
                $listSku[] = $rowSku;
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]
                        ),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE],
                        'website_id' => $this->getWebsiteId($rowData[self::COL_TIER_PRICE_WEBSITE])
                    ];
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($listSku) {
                    $this->processCountNewPrices($tierPrices);
                    if ($this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE)) {
                        $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                        $this->setUpdatedAt($listSku);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->processCountExistingPrices($tierPrices, self::TABLE_TIER_PRICE)
                    ->processCountNewPrices($tierPrices);
                $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                if ($listSku) {
                    $this->setUpdatedAt($listSku);
                }
            }
        }
        return $this;
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
            $tableName = $this->_resourceFactory->create()->getTable($table);
            $priceIn = [];
            $entityIds = [];
            $oldSkus = $this->retrieveOldSkus();
            foreach ($priceData as $sku => $priceRows) {
                if (isset($oldSkus[$sku])) {
                    $productId = $oldSkus[$sku];
                    foreach ($priceRows as $row) {
                        $row[$this->getProductEntityLinkField()] = $productId;
                        $priceIn[] = $row;
                        $entityIds[] = $productId;
                    }
                }
            }
            if ($priceIn) {
                $this->_connection->insertOnDuplicate($tableName, $priceIn, ['value']);
            }
        }
        return $this;
    }

    /**
     * Deletes tier prices prices.
     *
     * @param array $listSku
     * @param string $table
     * @return boolean
     */
    protected function deleteProductTierPrices(array $listSku, $table)
    {
        $tableName = $this->_resourceFactory->create()->getTable($table);
        $productEntityLinkField = $this->getProductEntityLinkField();
        if ($tableName && $listSku) {
            if (!$this->_cachedSkuToDelete) {
                $this->_cachedSkuToDelete = $this->_connection->fetchCol(
                    $this->_connection->select()
                        ->from($this->_catalogProductEntity, $productEntityLinkField)
                        ->where('sku IN (?)', $listSku)
                );
            }
            if ($this->_cachedSkuToDelete) {
                try {
                    $this->countItemsDeleted += $this->_connection->delete(
                        $tableName,
                        $this->_connection->quoteInto($productEntityLinkField . ' IN (?)', $this->_cachedSkuToDelete)
                    );
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, 0);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Set updated_at for product
     *
     * @param array $listSku
     * @return $this
     */
    protected function setUpdatedAt(array $listSku)
    {
        $updatedAt = $this->dateTime->gmtDate('Y-m-d H:i:s');
        $this->_connection->update(
            $this->_catalogProductEntity,
            [\Magento\Catalog\Model\Category::KEY_UPDATED_AT => $updatedAt],
            $this->_connection->quoteInto('sku IN (?)', array_unique($listSku))
        );
        return $this;
    }

    /**
     * Get website id by code
     *
     * @param string $websiteCode
     * @return array|int|string
     */
    protected function getWebSiteId($websiteCode)
    {
        $result = $websiteCode == $this->_getValidator(self::VALIDATOR_WEBSITE)->getAllWebsitesValue() ||
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
        $customerGroups = $this->_getValidator(self::VALIDATOR_TEAR_PRICE)->getCustomerGroups();
        return $customerGroup == self::VALUE_ALL_GROUPS ? 0 : $customerGroups[$customerGroup];
    }

    /**
     * Retrieve product skus
     *
     * @return array
     */
    protected function retrieveOldSkus()
    {
        if ($this->_oldSkus === null) {
            $this->_oldSkus = $this->_connection->fetchPairs(
                $this->_connection->select()->from(
                    $this->_catalogProductEntity,
                    ['sku', $this->getProductEntityLinkField()]
                )
            );
        }
        return $this->_oldSkus;
    }

    /**
     * Count existing prices
     *
     * @param array $prices
     * @param string $table
     * @return $this
     */
    protected function processCountExistingPrices($prices, $table)
    {
        $tableName = $this->_resourceFactory->create()->getTable($table);
        $productEntityLinkField = $this->getProductEntityLinkField();
        $existingPrices = $this->_connection->fetchAssoc(
            $this->_connection->select()->from(
                $tableName,
                ['value_id', $productEntityLinkField, 'all_groups', 'customer_group_id']
            )
        );
        $oldSkus = $this->retrieveOldSkus();
        foreach ($existingPrices as $existingPrice) {
            foreach ($oldSkus as $sku => $productId) {
                if ($existingPrice[$productEntityLinkField] == $productId && isset($prices[$sku])) {
                    $this->incrementCounterUpdated($prices[$sku], $existingPrice);
                }
            }
        }

        return $this;
    }

    /**
     * Increment counter of updated items
     *
     * @param array $prices
     * @param array $existingPrice
     * @return void
     */
    protected function incrementCounterUpdated($prices, $existingPrice)
    {
        foreach ($prices as $price) {
            if ($existingPrice['all_groups'] == $price['all_groups']
                && $existingPrice['customer_group_id'] == $price['customer_group_id']
            ) {
                $this->countItemsUpdated++;
            }
        }
    }

    /**
     * Count new prices
     *
     * @param array $tierPrices
     * @return $this
     */
    protected function processCountNewPrices(array $tierPrices)
    {
        foreach ($tierPrices as $productPrices) {
            $this->countItemsCreated += count($productPrices);
        }
        $this->countItemsCreated -= $this->countItemsUpdated;

        return $this;
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
}
