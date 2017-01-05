<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;

/**
 * Tier Price Validator.
 */
class TierPriceValidator
{
    /**
     * Groups by code cache.
     *
     * @var array
     */
    private $customerGroupsByCode = [];

    /**
     * @var TierPricePersistence
     */
    private $tierPricePersistence;

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
     * @param TierPricePersistence $tierPricePersistence
     * @param array $allowedProductTypes
     */
    public function __construct(
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        TierPricePersistence $tierPricePersistence,
        array $allowedProductTypes = []
    ) {
        $this->productIdLocator = $productIdLocator;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->websiteRepository = $websiteRepository;
        $this->tierPricePersistence = $tierPricePersistence;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * Validate SKU.
     *
     * @param array $skus
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validateSkus(array $skus)
    {
        $idsBySku = $this->productIdLocator->retrieveProductIdsBySkus($skus);
        $skuDiff = array_diff($skus, array_keys($idsBySku));

        foreach ($idsBySku as $sku => $ids) {
            foreach (array_values($ids) as $type) {
                if (!in_array($type, $this->allowedProductTypes)) {
                    $skuDiff[] = $sku;
                    break;
                }
            }
        }

        if (!empty($skuDiff)) {
            $values = implode(', ', $skuDiff);
            $description = count($skuDiff) == 1
                ? __('Requested product doesn\'t exist: %1', $values)
                : __('Requested products don\'t exist: %1', $values);
            throw new \Magento\Framework\Exception\NoSuchEntityException($description);
        }
    }

    /**
     * Validate that prices have appropriate values and are unique.
     *
     * @param array $prices
     * @param array $existingPrices
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validatePrices(array $prices, array $existingPrices = [])
    {
        $skus = array_unique(
            array_map(function ($price) {
                if (!$price->getSku()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            'Invalid attribute %fieldName: %fieldValue.',
                            [
                                'fieldName' => 'sku',
                                'fieldValue' => $price->getSku()
                            ]
                        )
                    );
                }
                return $price->getSku();
            }, $prices)
        );
        $this->validateSkus($skus);
        $idsBySku = $this->productIdLocator->retrieveProductIdsBySkus($skus);

        $pricesBySku = [];

        foreach ($prices as $price) {
            $pricesBySku[$price->getSku()][] = $price;
        }

        /** @var TierPriceInterface $price */
        foreach ($prices as $price) {
            $this->checkPrice($price);
            $this->checkPriceType($price, $idsBySku[$price->getSku()]);
            $this->checkQuantity($price);
            $this->checkWebsite($price);
            if (isset($pricesBySku[$price->getSku()])) {
                $this->checkUnique($price, $pricesBySku[$price->getSku()]);
            }
            $this->checkUnique($price, $existingPrices);
            $this->checkGroup($price);
        }
    }

    /**
     * Verify that price value is correct.
     *
     * @param TierPriceInterface $price
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function checkPrice(TierPriceInterface $price)
    {
        if (
            null === $price->getPrice()
            || $price->getPrice() < 0
            || ($price->getPriceType() === TierPriceInterface::PRICE_TYPE_DISCOUNT && $price->getPrice() > 100)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Invalid attribute %fieldName: %fieldValue.',
                    [
                        'fieldName' => 'Price',
                        'fieldValue' => $price->getPrice()
                    ]
                )
            );
        }
    }

    /**
     * Verify that price type is correct.
     *
     * @param TierPriceInterface $price
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function checkPriceType(TierPriceInterface $price, array $ids)
    {
        if (
            !in_array(
                $price->getPriceType(),
                [TierPriceInterface::PRICE_TYPE_FIXED, TierPriceInterface::PRICE_TYPE_DISCOUNT]
            )
            || (array_search(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, $ids)
                && $price->getPriceType() !== TierPriceInterface::PRICE_TYPE_DISCOUNT)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Invalid attribute %fieldName: %fieldValue.',
                    [
                        'fieldName' => 'Price Type',
                        'fieldValue' => $price->getPriceType()
                    ]
                )
            );
        }
    }

    /**
     * Verify that product quantity is correct.
     *
     * @param TierPriceInterface $price
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function checkQuantity(TierPriceInterface $price)
    {
        if ($price->getQuantity() < 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Invalid attribute %fieldName: %fieldValue.',
                    [
                        'fieldName' => 'Quantity',
                        'fieldValue' => $price->getQuantity()
                    ]
                )
            );
        }
    }

    /**
     * Verify that website exists.
     *
     * @param TierPriceInterface $price
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkWebsite(TierPriceInterface $price)
    {
        try {
            $this->websiteRepository->getById($price->getWebsiteId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Invalid attribute %fieldName: %fieldValue.',
                    [
                        'fieldName' => 'website_id',
                        'fieldValue' => $price->getWebsiteId()
                    ]
                )
            );
        }
    }

    /**
     * Check website value is unique.
     *
     * @param TierPriceInterface $tierPrice
     * @param array $prices
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkUnique(TierPriceInterface $tierPrice, array $prices)
    {
        /** @var TierPriceInterface $price */
        foreach ($prices as $price) {
            if (
                $price->getSku() === $tierPrice->getSku()
                && $price->getCustomerGroup() === $tierPrice->getCustomerGroup()
                && $price->getQuantity() == $tierPrice->getQuantity()
                && (
                    ($price->getWebsiteId() == $this->allWebsitesValue
                        || $tierPrice->getWebsiteId() == $this->allWebsitesValue)
                    && $price->getWebsiteId() != $tierPrice->getWebsiteId()
                )
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'We found a duplicate website, tier price, customer group and quantity: '
                        . '%fieldName1 = %fieldValue1, %fieldName2 = %fieldValue2, %fieldName3 = %fieldValue3.',
                        [
                            'fieldName1' => 'Customer Group',
                            'fieldValue1' => $price->getCustomerGroup(),
                            'fieldName2' => 'Website Id',
                            'fieldValue2' => $price->getWebsiteId(),
                            'fieldName3' => 'Quantity',
                            'fieldValue3' => $price->getQuantity()
                        ]
                    )
                );
            }
        }
    }

    /**
     * Check customer group exists and has correct value.
     *
     * @param TierPriceInterface $price
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    private function checkGroup(TierPriceInterface $price)
    {
        $customerGroup = strtolower($price->getCustomerGroup());

        if ($customerGroup != $this->allGroupsValue) {
            $this->retrieveGroupValue($customerGroup);
        }
    }

    /**
     * Retrieve customer group id by code.
     *
     * @param string $code
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue.',
                        [
                            'fieldName' => 'Customer Group',
                            'fieldValue' => $code
                        ]
                    )
                );
            }

            $this->customerGroupsByCode[strtolower($item->getCode())] = $item->getId();
        }

        return $this->customerGroupsByCode[$code];
    }
}
