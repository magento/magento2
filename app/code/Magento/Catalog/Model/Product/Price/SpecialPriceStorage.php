<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\SpecialPriceInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;
use Magento\Catalog\Api\SpecialPriceStorageInterface;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Data;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Special price storage presents efficient price API and is used to retrieve, update or delete special prices.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecialPriceStorage implements SpecialPriceStorageInterface
{
    /**
     * @var \Magento\Catalog\Api\SpecialPriceInterface
     */
    private $specialPriceResource;

    /**
     * @var SpecialPriceInterfaceFactory
     */
    private $specialPriceFactory;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Result
     */
    private $validationResult;

    /**
     * @var InvalidSkuProcessor
     */
    private $invalidSkuProcessor;

    /**
     * @var array
     */
    private $allowedProductTypes;

    /**
     * @var Data
     */
    private $catalogData;

    /**
     * @param \Magento\Catalog\Api\SpecialPriceInterface $specialPriceResource
     * @param SpecialPriceInterfaceFactory $specialPriceFactory
     * @param ProductIdLocatorInterface $productIdLocator
     * @param StoreRepositoryInterface $storeRepository
     * @param Result $validationResult
     * @param InvalidSkuProcessor $invalidSkuProcessor
     * @param array $allowedProductTypes
     * @param Data|null $catalogData
     */
    public function __construct(
        \Magento\Catalog\Api\SpecialPriceInterface $specialPriceResource,
        SpecialPriceInterfaceFactory $specialPriceFactory,
        ProductIdLocatorInterface $productIdLocator,
        StoreRepositoryInterface $storeRepository,
        Result $validationResult,
        InvalidSkuProcessor $invalidSkuProcessor,
        array $allowedProductTypes = [],
        ?Data $catalogData = null
    ) {
        $this->specialPriceResource = $specialPriceResource;
        $this->specialPriceFactory = $specialPriceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->validationResult = $validationResult;
        $this->invalidSkuProcessor = $invalidSkuProcessor;
        $this->allowedProductTypes = $allowedProductTypes;
        $this->catalogData = $catalogData ?: ObjectManager::getInstance()->get(Data::class);
    }

    /**
     * @inheritdoc
     */
    public function get(array $skus)
    {
        $skus = $this->invalidSkuProcessor->filterSkuList($skus, $this->allowedProductTypes);
        $rawPrices = $this->specialPriceResource->get($skus);

        $prices = [];
        foreach ($rawPrices as $rawPrice) {
            /** @var SpecialPriceInterface $price */
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
     * @inheritdoc
     */
    public function update(array $prices)
    {
        $prices = $this->retrieveValidPrices($prices);
        $this->specialPriceResource->update($prices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * @inheritdoc
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
        $failedSkus = $this->invalidSkuProcessor->retrieveInvalidSkuList($skus, $this->allowedProductTypes);

        foreach ($prices as $key => $price) {
            if (!$price->getSku() || in_array($price->getSku(), $failedSkus)) {
                $errorMessage = 'The product that was requested doesn\'t exist. Verify the product and try again. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.';
                $this->addFailedItemPrice($price, $key, $errorMessage, []);
            }
            $this->checkStore($price, $key);
            $this->checkPrice($price, $key);
            $this->checkDate($price, $price->getPriceFrom(), 'Price From', $key);
            $this->checkDate($price, $price->getPriceTo(), 'Price To', $key);
        }

        foreach ($this->validationResult->getFailedRowIds() as $id) {
            unset($prices[$id]);
        }

        return $prices;
    }

    /**
     * Check that store exists and is global when price scope is global and otherwise add error to aggregator.
     *
     * @param SpecialPriceInterface $price
     * @param int $key
     * @return void
     */
    private function checkStore(SpecialPriceInterface $price, int $key): void
    {
        if ($this->catalogData->isPriceGlobal() && $price->getStoreId() !== 0) {
            $errorMessage = 'Could not change non global Price when price scope is global. '
                . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.';
            $this->addFailedItemPrice($price, $key, $errorMessage, []);
        }

        try {
            $this->storeRepository->getById($price->getStoreId());
        } catch (NoSuchEntityException $e) {
            $errorMessage = 'Requested store is not found. '
                . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.';
            $this->addFailedItemPrice($price, $key, $errorMessage, []);
        }
    }

    /**
     * Check that date value is correct and add error to aggregator if it contains incorrect data.
     *
     * @param SpecialPriceInterface $price
     * @param string $value
     * @param string $label
     * @param int $key
     * @return void
     */
    private function checkDate(SpecialPriceInterface $price, $value, $label, $key)
    {
        if ($value && !$this->isCorrectDateValue($value)) {
            $errorMessage = 'Invalid attribute %label = %priceTo. '
                . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.';
            $this->addFailedItemPrice($price, $key, $errorMessage, ['label' => $label]);
        }
    }

    /**
     * Check price.
     *
     * Verify that provided price value is not empty and not lower then zero and add error to aggregator if price
     * contains not valid data.
     *
     * @param SpecialPriceInterface $price
     * @param int $key
     * @return void
     */
    private function checkPrice(SpecialPriceInterface $price, int $key): void
    {
        if (null === $price->getPrice() || $price->getPrice() < 0) {
            $errorMessage = 'Invalid attribute Price = %price. '
                . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.';
            $this->addFailedItemPrice($price, $key, $errorMessage, ['price' => $price->getPrice()]);
        }
    }

    /**
     * Adds failed item price to validation result
     *
     * @param SpecialPriceInterface $price
     * @param int $key
     * @param string $message
     * @param array $firstParam
     * @return void
     */
    private function addFailedItemPrice(
        SpecialPriceInterface $price,
        int $key,
        string $message,
        array $firstParam
    ): void {
        $additionalInfo = [];
        if ($firstParam) {
            $additionalInfo = array_merge($additionalInfo, $firstParam);
        }

        $additionalInfo['SKU'] = $price->getSku();
        $additionalInfo['storeId'] = $price->getStoreId();
        $additionalInfo['priceFrom'] = $price->getPriceFrom();
        $additionalInfo['priceTo'] = $price->getPriceTo();

        $this->validationResult->addFailedItem($key, __($message, $additionalInfo), $additionalInfo);
    }

    /**
     * Retrieve SKU by product ID.
     *
     * @param int $productId
     * @param array $skus
     * @return string|null
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
     */
    private function isCorrectDateValue($date)
    {
        $actualDate = date('Y-m-d H:i:s', strtotime($date));
        return $actualDate && $actualDate === $date;
    }
}
