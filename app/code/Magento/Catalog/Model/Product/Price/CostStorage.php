<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Product cost storage.
 * @since 2.2.0
 */
class CostStorage implements \Magento\Catalog\Api\CostStorageInterface
{
    /**
     * Attribute code.
     *
     * @var string
     * @since 2.2.0
     */
    private $attributeCode = 'cost';

    /**
     * @var PricePersistence
     * @since 2.2.0
     */
    private $pricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\CostInterfaceFactory
     * @since 2.2.0
     */
    private $costInterfaceFactory;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     * @since 2.2.0
     */
    private $productIdLocator;

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
     * Allowed product types.
     *
     * @var array
     * @since 2.2.0
     */
    private $allowedProductTypes = [];

    /**
     * @var PricePersistenceFactory
     * @since 2.2.0
     */
    private $pricePersistenceFactory;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     * @since 2.2.0
     */
    private $storeRepository;

    /**
     * CostStorage constructor.
     *
     * @param PricePersistenceFactory $pricePersistenceFactory
     * @param \Magento\Catalog\Api\Data\CostInterfaceFactory $costInterfaceFactory
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     * @param \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor $invalidSkuProcessor
     * @param array $allowedProductTypes [optional]
     * @since 2.2.0
     */
    public function __construct(
        PricePersistenceFactory $pricePersistenceFactory,
        \Magento\Catalog\Api\Data\CostInterfaceFactory $costInterfaceFactory,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult,
        \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor $invalidSkuProcessor,
        array $allowedProductTypes = []
    ) {
        $this->pricePersistenceFactory = $pricePersistenceFactory;
        $this->costInterfaceFactory = $costInterfaceFactory;
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
        $rawPrices = $this->getPricePersistence()->get($skus);
        $prices = [];
        foreach ($rawPrices as $rawPrice) {
            $price = $this->costInterfaceFactory->create();
            $sku = $this->getPricePersistence()
                ->retrieveSkuById($rawPrice[$this->getPricePersistence()->getEntityLinkField()], $skus);
            $price->setSku($sku);
            $price->setCost($rawPrice['value']);
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
            $productIdsBySkus = $this->productIdLocator->retrieveProductIdsBySkus([$price->getSku()]);
            $productIds = array_keys($productIdsBySkus[$price->getSku()]);
            foreach ($productIds as $id) {
                $formattedPrices[] = [
                    'store_id' => $price->getStoreId(),
                    $this->getPricePersistence()->getEntityLinkField() => $id,
                    'value' => $price->getCost(),
                ];
            }
        }

        $this->getPricePersistence()->update($formattedPrices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function delete(array $skus)
    {
        $skus = $this->invalidSkuProcessor->filterSkuList($skus, $this->allowedProductTypes);
        $this->getPricePersistence()->delete($skus);

        return true;
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
        $invalidSkus = $this->invalidSkuProcessor->retrieveInvalidSkuList($skus, $this->allowedProductTypes);

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
            if (null === $price->getCost() || $price->getCost() < 0) {
                $this->validationResult->addFailedItem(
                    $id,
                    __(
                        'Invalid attribute Cost = %cost. Row ID: SKU = %SKU, Store ID: %storeId.',
                        ['cost' => $price->getCost(), 'SKU' => $price->getSku(), 'storeId' => $price->getStoreId()]
                    ),
                    ['cost' => $price->getCost(), 'SKU' => $price->getSku(), 'storeId' => $price->getStoreId()]
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
