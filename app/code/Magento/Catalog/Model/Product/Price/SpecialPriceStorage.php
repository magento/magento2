<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Special price storage presents efficient price API and is used to retrieve, update or delete special prices.
 */
class SpecialPriceStorage implements \Magento\Catalog\Api\SpecialPriceStorageInterface
{
    /**
     * @var \Magento\Catalog\Api\SpecialPriceInterface
     */
    private $specialPriceResource;

    /**
     * @var \Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory
     */
    private $specialPriceFactory;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\InvalidSkuChecker
     */
    private $invalidSkuChecker;

    /**
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @param \Magento\Catalog\Api\SpecialPriceInterface $specialPriceResource
     * @param \Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory $specialPriceFactory
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     * @param \Magento\Catalog\Model\Product\Price\InvalidSkuChecker $invalidSkuChecker
     * @param array $allowedProductTypes [optional]
     */
    public function __construct(
        \Magento\Catalog\Api\SpecialPriceInterface $specialPriceResource,
        \Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory $specialPriceFactory,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult,
        \Magento\Catalog\Model\Product\Price\InvalidSkuChecker $invalidSkuChecker,
        array $allowedProductTypes = []
    ) {
        $this->specialPriceResource = $specialPriceResource;
        $this->specialPriceFactory = $specialPriceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->validationResult = $validationResult;
        $this->invalidSkuChecker = $invalidSkuChecker;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $skus)
    {
        $this->invalidSkuChecker->isSkuListValid($skus, $this->allowedProductTypes);
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
     */
    public function update(array $prices)
    {
        $prices = $this->retrieveValidPrices($prices);
        $this->specialPriceResource->update($prices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * {@inheritdoc}
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
     */
    private function retrieveValidPrices(array $prices)
    {
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $failedSkus = $this->invalidSkuChecker->retrieveInvalidSkuList($skus, $this->allowedProductTypes);

        foreach ($prices as $key => $price) {
            if (!$price->getSku() || in_array($price->getSku(), $failedSkus)) {
                $this->validationResult->addFailedItem(
                    $key,
                    __(
                        'Requested product doesn\'t exist: %sku',
                        ['sku' => '%sku']
                    ),
                    ['sku' => $price->getSku()]
                );
            }
            $this->checkPrice($price->getPrice(), $key);
            $this->checkDate($price->getPriceFrom(), 'Price From', $key);
            $this->checkDate($price->getPriceTo(), 'Price To', $key);
            try {
                $this->storeRepository->getById($price->getStoreId());
            } catch (NoSuchEntityException $e) {
                $this->validationResult->addFailedItem(
                    $key,
                    __('Requested store is not found.')
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
     * @param string $value
     * @param string $label
     * @param int $key
     * @return void
     */
    private function checkDate($value, $label, $key)
    {
        if ($value && !$this->isCorrectDateValue($value)) {
            $this->validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => $label, 'fieldValue' => $value]
            );
        }
    }

    /**
     * Check that provided price value is not empty and not lower then zero and add error to aggregator if price
     * contains not valid data.
     *
     * @param float $price
     * @param int $key
     * @return void
     */
    private function checkPrice($price, $key)
    {
        if (null === $price || $price < 0) {
            $this->validationResult->addFailedItem(
                $key,
                __(
                    'Invalid attribute %fieldName = %fieldValue.',
                    ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                ),
                ['fieldName' => 'Price', 'fieldValue' => $price]
            );
        }
    }

    /**
     * Retrieve SKU by product ID.
     *
     * @param int $id
     * @param array $skus
     * @return int|null
     */
    private function retrieveSkuById($id, array $skus)
    {
        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $sku => $ids) {
            if (false !== array_key_exists($id, $ids)) {
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
     */
    private function isCorrectDateValue($date)
    {
        $actualDate = date('Y-m-d H:i:s', strtotime($date));
        return $actualDate && $actualDate === $date;
    }
}
