<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product\Price\TierPricePersistence;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Validate Tier Price and check duplication
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceValidator
{
    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var Result
     */
    private $validationResult;

    /**
     * @var TierPricePersistence
     */
    private $tierPricePersistence;

    /**
     * Groups by code cache.
     *
     * @var array
     */
    private $customerGroupsByCode = [];

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
     * TierPriceValidator constructor.
     *
     * @param ProductIdLocatorInterface $productIdLocator
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param GroupRepositoryInterface $customerGroupRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param TierPricePersistence $tierPricePersistence
     * @param Result $validationResult
     * @param InvalidSkuProcessor $invalidSkuProcessor
     * @param array $allowedProductTypes [optional]
     */
    public function __construct(
        ProductIdLocatorInterface          $productIdLocator,
        SearchCriteriaBuilder              $searchCriteriaBuilder,
        FilterBuilder                      $filterBuilder,
        GroupRepositoryInterface            $customerGroupRepository,
        WebsiteRepositoryInterface             $websiteRepository,
        TierPricePersistence $tierPricePersistence,
        Result                                                    $validationResult,
        InvalidSkuProcessor                                       $invalidSkuProcessor,
        array                                                     $allowedProductTypes = []
    ) {
        $this->productIdLocator = $productIdLocator;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->websiteRepository = $websiteRepository;
        $this->tierPricePersistence = $tierPricePersistence;
        $this->validationResult = $validationResult;
        $this->invalidSkuProcessor = $invalidSkuProcessor;
        $this->allowedProductTypes = $allowedProductTypes;
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
        if ($price->getQuantity() < 1) {
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
    private function checkWebsite(TierPriceInterface $price, $key, Result $validationResult)
    {
        try {
            $this->websiteRepository->getById($price->getWebsiteId());
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
                        : ($price->getWebsiteId() == $tierPrice->getWebsiteId());
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
        if (!isset($this->customerGroupsByCode[$code])) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField('customer_group_code')->setValue($code)->create()
                ]
            );
            $items = $this->customerGroupRepository->getList($searchCriteria->create())->getItems();
            $item = array_shift($items);

            if (!$item) {
                return false;
            }

            $this->customerGroupsByCode[strtolower($item->getCode())] = $item->getId();
        }

        return $this->customerGroupsByCode[$code];
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
}
