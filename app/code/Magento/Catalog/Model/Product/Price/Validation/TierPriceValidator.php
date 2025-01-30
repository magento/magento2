<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Validate Tier Price and check duplication
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceValidator implements ResetAfterRequestInterface
{
    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var Result
     */
    private $validationResult;

    /**
     * @var InvalidSkuProcessor
     */
    private $invalidSkuProcessor;

    /**
     * @var string
     */
    private $allGroupsValue = 'all groups';

    /**
     * @var string
     */
    private $allWebsitesValue = "0";

    /**
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $productsCacheBySku = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array Customer group check cache
     */
    private $customerGroupCheck = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * TierPriceValidator constructor.
     *
     * @param ProductIdLocatorInterface $productIdLocator
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param Result $validationResult
     * @param InvalidSkuProcessor $invalidSkuProcessor
     * @param ProductRepositoryInterface $productRepository
     * @param array $allowedProductTypes [optional]
     * @param ResourceConnection|null $resourceConnection
     * @param ScopeConfigInterface|null $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductIdLocatorInterface $productIdLocator,
        WebsiteRepositoryInterface $websiteRepository,
        Result $validationResult,
        InvalidSkuProcessor $invalidSkuProcessor,
        ProductRepositoryInterface $productRepository,
        array $allowedProductTypes = [],
        ?ResourceConnection $resourceConnection = null,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->productIdLocator = $productIdLocator;
        $this->websiteRepository = $websiteRepository;
        $this->validationResult = $validationResult;
        $this->invalidSkuProcessor = $invalidSkuProcessor;
        $this->productRepository = $productRepository;
        $this->allowedProductTypes = $allowedProductTypes;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Validate SKU.
     *
     * @param array $skus
     * @return array
     */
    public function validateSkus(array $skus)
    {
        return $this->invalidSkuProcessor->filterSkuList($skus, $this->allowedProductTypes);
    }

    /**
     * Validate that prices have appropriate values and are unique and return result.
     *
     * @param array $prices
     * @param array $existingPrices
     * @return Result $validationResult
     */
    public function retrieveValidationResult(array $prices, array $existingPrices = [])
    {
        $validationResult = clone $this->validationResult;
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $skuDiff = $this->invalidSkuProcessor->retrieveInvalidSkuList($skus, $this->allowedProductTypes);
        $idsBySku = $this->productIdLocator->retrieveProductIdsBySkus($skus);

        $pricesBySku = [];

        foreach ($prices as $price) {
            $pricesBySku[$price->getSku()][] = $price;
        }

        foreach ($prices as $key => $price) {
            $this->checkSku($price, $key, $skuDiff, $validationResult);
            $this->checkPrice($price, $key, $validationResult);
            $ids = isset($idsBySku[$price->getSku()]) ? $idsBySku[$price->getSku()] : [];
            $this->checkPriceType($price, $ids, $key, $validationResult);
            $this->checkQuantity($price, $key, $validationResult);
            $this->checkWebsite($price, $key, $validationResult);
            if (isset($pricesBySku[$price->getSku()])) {
                $this->checkUnique($price, $pricesBySku, $key, $validationResult);
            }
            $this->checkUnique($price, $existingPrices, $key, $validationResult, true);
            $this->checkGroup($price, $key, $validationResult);
        }

        return $validationResult;
    }

    /**
     * Check that sku value is correct.
     *
     * @param TierPriceInterface $price
     * @param int $key
     * @param array $invalidSkus
     * @param Result $validationResult
     * @return void
     */
    private function checkSku(
        TierPriceInterface $price,
        $key,
        array $invalidSkus,
        Result $validationResult
    ) {
        if (!$price->getSku() || in_array($price->getSku(), $invalidSkus)) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute SKU = %SKU. '
                    . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.',
                    [
                        'SKU' => '%SKU',
                        'websiteId' => '%websiteId',
                        'customerGroup' => '%customerGroup',
                        'qty' => '%qty'
                    ]
                ),
                [
                    'SKU' => $price->getSku(),
                    'websiteId' => $price->getWebsiteId(),
                    'customerGroup' => $price->getCustomerGroup(),
                    'qty' => $price->getQuantity()
                ]
            );
        }
    }

    /**
     * Verify that price value is correct.
     *
     * @param TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkPrice(TierPriceInterface $price, $key, Result $validationResult)
    {
        if (null === $price->getPrice()
            || $price->getPrice() < 0
            || ($price->getPriceType() === TierPriceInterface::PRICE_TYPE_DISCOUNT
                && $price->getPrice() > 100
            )
        ) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute Price = %price. '
                    . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.',
                    [
                        'price' => '%price',
                        'SKU' => '%SKU',
                        'websiteId' => '%websiteId',
                        'customerGroup' => '%customerGroup',
                        'qty' => '%qty'
                    ]
                ),
                [
                    'price' => $price->getPrice(),
                    'SKU' => $price->getSku(),
                    'websiteId' => $price->getWebsiteId(),
                    'customerGroup' => $price->getCustomerGroup(),
                    'qty' => $price->getQuantity()
                ]
            );
        }
    }

    /**
     * Verify that price type is correct.
     *
     * @param TierPriceInterface $price
     * @param array $ids
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkPriceType(
        TierPriceInterface $price,
        array $ids,
        $key,
        Result $validationResult
    ) {
        if (!in_array(
            $price->getPriceType(),
            [
                    TierPriceInterface::PRICE_TYPE_FIXED,
                    TierPriceInterface::PRICE_TYPE_DISCOUNT
                ]
        )
            || (array_search(Type::TYPE_BUNDLE, $ids) !== false
                && $price->getPriceType() !== TierPriceInterface::PRICE_TYPE_DISCOUNT)
        ) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute Price Type = %priceType. '
                    . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.',
                    [
                        'price' => '%price',
                        'SKU' => '%SKU',
                        'websiteId' => '%websiteId',
                        'customerGroup' => '%customerGroup',
                        'qty' => '%qty'
                    ]
                ),
                [
                    'priceType' => $price->getPriceType(),
                    'SKU' => $price->getSku(),
                    'websiteId' => $price->getWebsiteId(),
                    'customerGroup' => $price->getCustomerGroup(),
                    'qty' => $price->getQuantity()
                ]
            );
        }
    }

    /**
     * Verify that product quantity is correct.
     *
     * @param TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkQuantity(TierPriceInterface $price, $key, Result $validationResult)
    {
        $sku = $price->getSku();
        if (isset($this->productsCacheBySku[$sku])) {
            $product = $this->productsCacheBySku[$sku];
        } else {
            $product = $this->productRepository->get($price->getSku());
            $this->productsCacheBySku[$sku] = $product;
        }

        $canUseQtyDecimals = $product->getTypeInstance()->canUseQtyDecimals();
        if ($price->getQuantity() <= 0 || $price->getQuantity() < 1 && !$canUseQtyDecimals) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute Quantity = %qty. '
                    . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.',
                    [
                        'SKU' => '%SKU',
                        'websiteId' => '%websiteId',
                        'customerGroup' => '%customerGroup',
                        'qty' => '%qty'
                    ]
                ),
                [
                    'SKU' => $price->getSku(),
                    'websiteId' => $price->getWebsiteId(),
                    'customerGroup' => $price->getCustomerGroup(),
                    'qty' => $price->getQuantity()
                ]
            );
        }
    }

    /**
     * Verify that website exists.
     *
     * @param TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkWebsite(TierPriceInterface $price, $key, Result $validationResult): void
    {
        try {
            $this->websiteRepository->getById($price->getWebsiteId());
            $isWebsiteScope = $this->scopeConfig
                ->isSetFlag(
                    Data::XML_PATH_PRICE_SCOPE,
                    ScopeInterface::SCOPE_STORE,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            if (!$isWebsiteScope && (int) $this->allWebsitesValue !== $price->getWebsiteId()) {
                throw NoSuchEntityException::singleField('website_id', $price->getWebsiteId());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute Website ID = %websiteId. '
                    . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.',
                    [
                        'SKU' => '%SKU',
                        'websiteId' => '%websiteId',
                        'customerGroup' => '%customerGroup',
                        'qty' => '%qty'
                    ]
                ),
                [
                    'SKU' => $price->getSku(),
                    'websiteId' => $price->getWebsiteId(),
                    'customerGroup' => $price->getCustomerGroup(),
                    'qty' => $price->getQuantity()
                ]
            );
        }
    }

    /**
     * Check website value is unique.
     *
     * @param TierPriceInterface $tierPrice
     * @param array $prices
     * @param int $key
     * @param Result $validationResult
     * @param bool $isExistingPrice
     * @return void
     */
    private function checkUnique(
        TierPriceInterface $tierPrice,
        array $prices,
        $key,
        Result $validationResult,
        bool $isExistingPrice = false
    ) {
        if (isset($prices[$tierPrice->getSku()])) {
            foreach ($prices[$tierPrice->getSku()] as $price) {
                if ($price !== $tierPrice) {
                    $checkWebsiteValue = $isExistingPrice ? $this->compareWebsiteValue($price, $tierPrice)
                        : $this->compareWebsiteValueNewPrice($price, $tierPrice);
                    if (strtolower($price->getCustomerGroup()) === strtolower($tierPrice->getCustomerGroup())
                        && $price->getQuantity() == $tierPrice->getQuantity()
                        && $checkWebsiteValue
                    ) {
                        $validationResult->addFailedItem(
                            $key,
                            __(
                                'We found a duplicate website, tier price, customer group and quantity: '
                                . 'Customer Group = %customerGroup, Website ID = %websiteId, Quantity = %qty. '
                                . 'Row ID: SKU = %SKU, Website ID: %websiteId, '
                                . 'Customer Group: %customerGroup, Quantity: %qty.',
                                [
                                    'SKU' => '%SKU',
                                    'websiteId' => '%websiteId',
                                    'customerGroup' => '%customerGroup',
                                    'qty' => '%qty'
                                ]
                            ),
                            [
                                'SKU' => $price->getSku(),
                                'websiteId' => $price->getWebsiteId(),
                                'customerGroup' => $price->getCustomerGroup(),
                                'qty' => $price->getQuantity()
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Check customer group exists and has correct value.
     *
     * @param TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     * @throws LocalizedException
     */
    private function checkGroup(TierPriceInterface $price, $key, Result $validationResult)
    {
        $customerGroup = strtolower($price->getCustomerGroup());

        if ($customerGroup != $this->allGroupsValue && false === $this->retrieveGroupValue($customerGroup)) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'No such entity with Customer Group = %customerGroup. '
                    . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.',
                    [
                        'SKU' => '%SKU',
                        'websiteId' => '%websiteId',
                        'customerGroup' => '%customerGroup',
                        'qty' => '%qty'
                    ]
                ),
                [
                    'SKU' => $price->getSku(),
                    'websiteId' => $price->getWebsiteId(),
                    'customerGroup' => $price->getCustomerGroup(),
                    'qty' => $price->getQuantity()
                ]
            );
        }
    }

    /**
     * Retrieve customer group id by code.
     *
     * @param string $code
     * @return int|bool
     * @throws LocalizedException
     */
    private function retrieveGroupValue(string $code)
    {
        if (!isset($this->customerGroupCheck[$code])) {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()->from(
                $this->resourceConnection->getTableName('customer_group'),
                'customer_group_id'
            )->where(
                'customer_group_code = ?',
                $code
            );
            $this->customerGroupCheck[$code] = $connection->fetchOne($select);
        }

        return $this->customerGroupCheck[$code];
    }

    /**
     * Compare Website Values between price and tier price
     *
     * @param TierPriceInterface $price
     * @param TierPriceInterface $tierPrice
     * @return bool
     */
    private function compareWebsiteValue(TierPriceInterface $price, TierPriceInterface $tierPrice): bool
    {
        return (
                    $price->getWebsiteId() == $this->allWebsitesValue
                    || $tierPrice->getWebsiteId() == $this->allWebsitesValue
                )
                && $price->getWebsiteId() != $tierPrice->getWebsiteId();
    }

    /**
     * Compare Website Values between for new price records
     *
     * @param TierPriceInterface $price
     * @param TierPriceInterface $tierPrice
     * @return bool
     */
    private function compareWebsiteValueNewPrice(TierPriceInterface $price, TierPriceInterface $tierPrice): bool
    {
        if ($price->getWebsiteId() == $this->allWebsitesValue ||
            $tierPrice->getWebsiteId() == $this->allWebsitesValue
        ) {
            $baseCurrency = $this->scopeConfig->getValue(Currency::XML_PATH_CURRENCY_BASE, 'default');
            $websiteId = max($price->getWebsiteId(), $tierPrice->getWebsiteId());
            $website = $this->websiteRepository->getById($websiteId);
            $websiteCurrency = $website->getBaseCurrencyCode();

            return $baseCurrency == $websiteCurrency;
        }

        return $price->getWebsiteId() == $tierPrice->getWebsiteId();
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->customerGroupCheck = [];
    }
}
