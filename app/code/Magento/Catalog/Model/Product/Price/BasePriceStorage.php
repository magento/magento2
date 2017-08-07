<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Base prices storage.
 * @since 2.2.0
 */
class BasePriceStorage implements \Magento\Catalog\Api\BasePriceStorageInterface
{
    /**
     * Attribute code.
     *
     * @var string
     * @since 2.2.0
     */
    private $attributeCode = 'price';

    /**
     * @var PricePersistence
     * @since 2.2.0
     */
    private $pricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\BasePriceInterfaceFactory
     * @since 2.2.0
     */
    private $basePriceInterfaceFactory;

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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.2.0
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result
     * @since 2.2.0
     */
    private $validationResult;

    /**
     * @var PricePersistenceFactory
     * @since 2.2.0
     */
    private $pricePersistenceFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor
     * @since 2.2.0
     */
    private $invalidSkuProcessor;

    /**
     * Price type allowed.
     *
     * @var int
     * @since 2.2.0
     */
    private $priceTypeAllowed = 1;

    /**
     * Allowed product types.
     *
     * @var array
     * @since 2.2.0
     */
    private $allowedProductTypes = [];

    /**
     * @param PricePersistenceFactory $pricePersistenceFactory
     * @param \Magento\Catalog\Api\Data\BasePriceInterfaceFactory $basePriceInterfaceFactory
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     * @param \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor $invalidSkuProcessor
     * @param array $allowedProductTypes [optional]
     * @since 2.2.0
     */
    public function __construct(
        PricePersistenceFactory $pricePersistenceFactory,
        \Magento\Catalog\Api\Data\BasePriceInterfaceFactory $basePriceInterfaceFactory,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult,
        \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor $invalidSkuProcessor,
        array $allowedProductTypes = []
    ) {
        $this->pricePersistenceFactory = $pricePersistenceFactory;
        $this->basePriceInterfaceFactory = $basePriceInterfaceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->validationResult = $validationResult;
        $this->allowedProductTypes = $allowedProductTypes;
        $this->invalidSkuProcessor = $invalidSkuProcessor;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function get(array $skus)
    {
        $skus = $this->invalidSkuProcessor->filterSkuList(
            $skus,
            $this->allowedProductTypes,
            $this->priceTypeAllowed
        );
        $rawPrices = $this->getPricePersistence()->get($skus);
        $prices = [];
        foreach ($rawPrices as $rawPrice) {
            $price = $this->basePriceInterfaceFactory->create();
            $sku = $this->getPricePersistence()
                ->retrieveSkuById($rawPrice[$this->getPricePersistence()->getEntityLinkField()], $skus);
            $price->setSku($sku);
            $price->setPrice($rawPrice['value']);
            $price->setStoreId($rawPrice['store_id']);
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
        $formattedPrices = [];

        foreach ($prices as $price) {
            $ids = array_keys($this->productIdLocator->retrieveProductIdsBySkus([$price->getSku()])[$price->getSku()]);
            foreach ($ids as $id) {
                $formattedPrices[] = [
                    'store_id' => $price->getStoreId(),
                    $this->getPricePersistence()->getEntityLinkField() => $id,
                    'value' => $price->getPrice(),
                ];
            }
        }

        $this->getPricePersistence()->update($formattedPrices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * Get price persistence.
     *
     * @return PricePersistence
     * @since 2.2.0
     */
    private function getPricePersistence()
    {
        if (!$this->pricePersistence) {
            $this->pricePersistence = $this->pricePersistenceFactory->create(['attributeCode' => $this->attributeCode]);
        }

        return $this->pricePersistence;
    }

    /**
     * Retrieve valid prices that do not contain any errors.
     *
     * @param \Magento\Catalog\Api\Data\BasePriceInterface[] $prices
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
        $invalidSkus = $this->invalidSkuProcessor->retrieveInvalidSkuList(
            $skus,
            $this->allowedProductTypes,
            $this->priceTypeAllowed
        );

        foreach ($prices as $id => $price) {
            if (!$price->getSku() || in_array($price->getSku(), $invalidSkus)) {
                $this->validationResult->addFailedItem(
                    $id,
                    __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ),
                    ['fieldName' => 'SKU', 'fieldValue' => $price->getSku()]
                );
            }
            if (null === $price->getPrice() || $price->getPrice() < 0) {
                $this->validationResult->addFailedItem(
                    $id,
                    __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ),
                    ['fieldName' => 'Price', 'fieldValue' => $price->getPrice()]
                );
            }
            try {
                $this->storeRepository->getById($price->getStoreId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->validationResult->addFailedItem(
                    $id,
                    __(
                        'Requested store is not found. Row ID: SKU = %SKU, Store ID: %storeId.',
                        ['SKU' => $price->getSku(), 'storeId' => $price->getStoreId()]
                    ),
                    ['SKU' => $price->getSku(), 'storeId' => $price->getStoreId()]
                );
            }
        }

        foreach ($this->validationResult->getFailedRowIds() as $id) {
            unset($prices[$id]);
        }

        return $prices;
    }
}
