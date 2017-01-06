<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price\Validation;

/**
 * Tier Price Validator.
 */
class TierPriceValidator
{
    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPricePersistence
     */
    private $tierPricePersistence;

    /**
     * Groups by code cache.
     *
     * @var array
     */
    private $customerGroupsByCode = [];

    /**
     * @var \Magento\Catalog\Model\Product\Price\InvalidSkuChecker
     */
    private $invalidSkuChecker;

    /**
     * All groups value.
     *
     * @var string
     */
    private $allGroupsValue = 'all groups';

    /**
     * All websites value.
     *
     * @var string
     */
    private $allWebsitesValue = "0";

    /**
     * Allowed product types.
     *
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * TierPriceValidator constructor.
     *
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Catalog\Model\Product\Price\TierPricePersistence $tierPricePersistence
     * @param \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     * @param \Magento\Catalog\Model\Product\Price\InvalidSkuChecker $invalidSkuChecker
     * @param array $allowedProductTypes [optional]
     */
    public function __construct(
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Catalog\Model\Product\Price\TierPricePersistence $tierPricePersistence,
        \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult,
        \Magento\Catalog\Model\Product\Price\InvalidSkuChecker $invalidSkuChecker,
        array $allowedProductTypes = []
    ) {
        $this->productIdLocator = $productIdLocator;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->websiteRepository = $websiteRepository;
        $this->tierPricePersistence = $tierPricePersistence;
        $this->validationResult = $validationResult;
        $this->invalidSkuChecker = $invalidSkuChecker;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * Validate SKU.
     *
     * @param array $skus
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function validateSkus(array $skus)
    {
        $this->invalidSkuChecker->isSkuListValid($skus, $this->allowedProductTypes);
    }

    /**
     * Validate that prices have appropriate values and are unique and return result.
     *
     * @param array $prices
     * @param array $existingPrices
     * @return \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     */
    public function retrieveValidationResult(array $prices, array $existingPrices = [])
    {
        $validationResult = clone $this->validationResult;
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $skuDiff = $this->invalidSkuChecker->retrieveInvalidSkuList($skus, $this->allowedProductTypes);
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
                $this->checkUnique($price, $pricesBySku[$price->getSku()], $key, $validationResult);
            }
            $this->checkUnique($price, $existingPrices, $key, $validationResult);
            $this->checkGroup($price, $key, $validationResult);
        }

        return $validationResult;
    }

    /**
     * Check that sku value is correct.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $price
     * @param int $key
     * @param array $invalidSkus
     * @param Result $validationResult
     * @return void
     */
    private function checkSku(
        \Magento\Catalog\Api\Data\TierPriceInterface $price,
        $key,
        array $invalidSkus,
        Result $validationResult
    ) {
        if (!$price->getSku() || in_array($price->getSku(), $invalidSkus)) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => 'SKU', 'fieldValue' => $price->getSku()]
            );
        }
    }

    /**
     * Verify that price value is correct.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkPrice(\Magento\Catalog\Api\Data\TierPriceInterface $price, $key, Result $validationResult)
    {
        if (
            null === $price->getPrice()
            || $price->getPrice() < 0
            || ($price->getPriceType() === \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT
                && $price->getPrice() > 100
            )
        ) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => 'Price', 'fieldValue' => $price->getPrice()]
            );
        }
    }

    /**
     * Verify that price type is correct.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $price
     * @param array $ids
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkPriceType(
        \Magento\Catalog\Api\Data\TierPriceInterface $price,
        array $ids,
        $key,
        Result $validationResult
    ) {
        if (
            !in_array(
                $price->getPriceType(),
                [
                    \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED,
                    \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT
                ]
            )
            || (array_search(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, $ids)
                && $price->getPriceType() !== \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT)
        ) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => 'Price Type', 'fieldValue' => $price->getPriceType()]
            );
        }
    }

    /**
     * Verify that product quantity is correct.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkQuantity(\Magento\Catalog\Api\Data\TierPriceInterface $price, $key, Result $validationResult)
    {
        if ($price->getQuantity() < 1) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => 'Quantity', 'fieldValue' => $price->getQuantity()]
            );
        }
    }

    /**
     * Verify that website exists.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkWebsite(\Magento\Catalog\Api\Data\TierPriceInterface $price, $key, Result $validationResult)
    {
        try {
            $this->websiteRepository->getById($price->getWebsiteId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => 'Website Id', 'fieldValue' => $price->getWebsiteId()]
            );
        }
    }

    /**
     * Check website value is unique.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $tierPrice
     * @param array $prices
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkUnique(
        \Magento\Catalog\Api\Data\TierPriceInterface $tierPrice,
        array $prices,
        $key,
        Result $validationResult
    ) {
        foreach ($prices as $price) {
            if (
                $price->getSku() === $tierPrice->getSku()
                && strtolower($price->getCustomerGroup()) === strtolower($tierPrice->getCustomerGroup())
                && $price->getQuantity() == $tierPrice->getQuantity()
                && (
                    ($price->getWebsiteId() == $this->allWebsitesValue
                        || $tierPrice->getWebsiteId() == $this->allWebsitesValue)
                    && $price->getWebsiteId() != $tierPrice->getWebsiteId()
                )
            ) {
                $validationResult->addFailedItem(
                    $key,
                    __(
                        'We found a duplicate website, tier price, customer group and quantity:'
                        . ' %fieldName1 = %fieldValue1, %fieldName2 = %fieldValue2, %fieldName3 = %fieldValue3.',
                        [
                            'fieldName1' => '%fieldName1',
                            'fieldValue1' => '%fieldValue1',
                            'fieldName2' => '%fieldName2',
                            'fieldValue2' => '%fieldValue2',
                            'fieldName3' => '%fieldName3',
                            'fieldValue3' => '%fieldValue3'
                        ]
                    ),
                    [
                        'fieldName1' => 'Customer Group',
                        'fieldValue1' => $price->getCustomerGroup(),
                        'fieldName2' => 'Website Id',
                        'fieldValue2' => $price->getWebsiteId(),
                        'fieldName3' => 'Quantity',
                        'fieldValue3' => $price->getQuantity(),
                    ]
                );
            }
        }
    }

    /**
     * Check customer group exists and has correct value.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface $price
     * @param int $key
     * @param Result $validationResult
     * @return void
     */
    private function checkGroup(\Magento\Catalog\Api\Data\TierPriceInterface $price, $key, Result $validationResult)
    {
        $customerGroup = strtolower($price->getCustomerGroup());

        if ($customerGroup != $this->allGroupsValue && false === $this->retrieveGroupValue($customerGroup)) {
            $validationResult->addFailedItem(
                $key,
                __(
                    'No such entity with %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                [
                    'fieldName' => 'Customer Group',
                    'fieldValue' => $customerGroup,
                ]
            );
        }
    }

    /**
     * Retrieve customer group id by code.
     *
     * @param string $code
     * @return int|bool
     */
    private function retrieveGroupValue($code)
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
}
