<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Special price storage presents efficient price API and is used to retrieve, update or delete special prices.
 * @since 2.2.0
 */
class SpecialPriceStorage implements \Magento\Catalog\Api\SpecialPriceStorageInterface
{
    /**
     * @var \Magento\Catalog\Api\SpecialPriceInterface
     * @since 2.2.0
     */
    private $specialPriceResource;

    /**
     * @var \Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory
     * @since 2.2.0
     */
    private $specialPriceFactory;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     * @since 2.2.0
     */
    private $productIdLocator;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     * @since 2.2.0
     */
    private $storeRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result
     * @since 2.2.0
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor
     * @since 2.2.0
     */
    private $invalidSkuProcessor;

    /**
     * @var array
     * @since 2.2.0
     */
    private $allowedProductTypes = [];

    /**
     * @param \Magento\Catalog\Api\SpecialPriceInterface $specialPriceResource
     * @param \Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory $specialPriceFactory
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     * @param \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor $invalidSkuProcessor
     * @param array $allowedProductTypes [optional]
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Catalog\Api\SpecialPriceInterface $specialPriceResource,
        \Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory $specialPriceFactory,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult,
        \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor $invalidSkuProcessor,
        array $allowedProductTypes = []
    ) {
        $this->specialPriceResource = $specialPriceResource;
        $this->specialPriceFactory = $specialPriceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->validationResult = $validationResult;
        $this->invalidSkuProcessor = $invalidSkuProcessor;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function get(array $skus)
    {
        $skus = $this->invalidSkuProcessor->filterSkuList($skus, $this->allowedProductTypes);
        $rawPrices = $this->specialPriceResource->get($skus);

        $prices = [];
        foreach ($rawPrices as $rawPrice) {
            /** @var \Magento\Catalog\Api\Data\SpecialPriceInterface $price */
            $price = $this->specialPriceFactory->create();
            $sku = isset($rawPrice['sku'])
                ? $rawPrice['sku']
                : $this->retrieveSkuById($rawPrice[$this->specialPriceResource->getEntityLinkField()], $skus);
            $price->setSku($sku);
            $price->setPrice($rawPrice['value']);
            $price->setStoreId($rawPrice['store_id']);
            $price->setPriceFrom($rawPrice['price_from']);
            $price->setPriceTo($rawPrice['price_to']);
            $prices[] = $price;
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function update(array $prices)
    {
        $prices = $this->retrieveValidPrices($prices);
        $this->specialPriceResource->update($prices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function delete(array $prices)
    {
        $prices = $this->retrieveValidPrices($prices);
        $this->specialPriceResource->delete($prices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * Retrieve prices with correct values.
     *
     * @param array $prices
     * @return array
     * @since 2.2.0
     */
    private function retrieveValidPrices(array $prices)
    {
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $failedSkus = $this->invalidSkuProcessor->retrieveInvalidSkuList($skus, $this->allowedProductTypes);

        foreach ($prices as $key => $price) {
            if (!$price->getSku() || in_array($price->getSku(), $failedSkus)) {
                $this->validationResult->addFailedItem(
                    $key,
                    __(
                        'Requested product doesn\'t exist. '
                        . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                        [
                            'SKU' => $price->getSku(),
                            'storeId' => $price->getStoreId(),
                            'priceFrom' => $price->getPriceFrom(),
                            'priceTo' => $price->getPriceTo()
                        ]
                    ),
                    [
                        'SKU' => $price->getSku(),
                        'storeId' => $price->getStoreId(),
                        'priceFrom' => $price->getPriceFrom(),
                        'priceTo' => $price->getPriceTo()
                    ]
                );
            }
            $this->checkPrice($price, $key);
            $this->checkDate($price, $price->getPriceFrom(), 'Price From', $key);
            $this->checkDate($price, $price->getPriceTo(), 'Price To', $key);
            try {
                $this->storeRepository->getById($price->getStoreId());
            } catch (NoSuchEntityException $e) {
                $this->validationResult->addFailedItem(
                    $key,
                    __(
                        'Requested store is not found. '
                        . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                        [
                            'SKU' => $price->getSku(),
                            'storeId' => $price->getStoreId(),
                            'priceFrom' => $price->getPriceFrom(),
                            'priceTo' => $price->getPriceTo()
                        ]
                    ),
                    [
                        'SKU' => $price->getSku(),
                        'storeId' => $price->getStoreId(),
                        'priceFrom' => $price->getPriceFrom(),
                        'priceTo' => $price->getPriceTo()
                    ]
                );
            }
        }

        foreach ($this->validationResult->getFailedRowIds() as $id) {
            unset($prices[$id]);
        }

        return $prices;
    }

    /**
     * Check that date value is correct and add error to aggregator if it contains incorrect data.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceInterface $price
     * @param string $value
     * @param string $label
     * @param int $key
     * @return void
     * @since 2.2.0
     */
    private function checkDate(\Magento\Catalog\Api\Data\SpecialPriceInterface $price, $value, $label, $key)
    {
        if ($value && !$this->isCorrectDateValue($value)) {
            $this->validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %label = %priceTo. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'label' => $label,
                        'SKU' => $price->getSku(),
                        'storeId' => $price->getStoreId(),
                        'priceFrom' => $price->getPriceFrom(),
                        'priceTo' => $price->getPriceTo()
                    ]
                ),
                [
                    'label' => $label,
                    'SKU' => $price->getSku(),
                    'storeId' => $price->getStoreId(),
                    'priceFrom' => $price->getPriceFrom(),
                    'priceTo' => $price->getPriceTo()
                ]
            );
        }
    }

    /**
     * Check that provided price value is not empty and not lower then zero and add error to aggregator if price
     * contains not valid data.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceInterface $price
     * @param int $key
     * @return void
     * @since 2.2.0
     */
    private function checkPrice(\Magento\Catalog\Api\Data\SpecialPriceInterface $price, $key)
    {
        if (null === $price->getPrice() || $price->getPrice() < 0) {
            $this->validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute Price = %price. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'price' => $price->getPrice(),
                        'SKU' => $price->getSku(),
                        'storeId' => $price->getStoreId(),
                        'priceFrom' => $price->getPriceFrom(),
                        'priceTo' => $price->getPriceTo()
                    ]
                ),
                [
                    'price' => $price->getPrice(),
                    'SKU' => $price->getSku(),
                    'storeId' => $price->getStoreId(),
                    'priceFrom' => $price->getPriceFrom(),
                    'priceTo' => $price->getPriceTo()
                ]
            );
        }
    }

    /**
     * Retrieve SKU by product ID.
     *
     * @param int $productId
     * @param array $skus
     * @return string|null
     * @since 2.2.0
     */
    private function retrieveSkuById($productId, array $skus)
    {
        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $sku => $ids) {
            if (isset($ids[$productId])) {
                return $sku;
            }
        }

        return null;
    }

    /**
     * Check that date value is correct.
     *
     * @param string $date
     * @return bool
     * @since 2.2.0
     */
    private function isCorrectDateValue($date)
    {
        $actualDate = date('Y-m-d H:i:s', strtotime($date));
        return $actualDate && $actualDate === $date;
    }
}
