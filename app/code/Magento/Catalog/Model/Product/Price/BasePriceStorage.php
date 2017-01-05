<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Base prices storage.
 */
class BasePriceStorage implements \Magento\Catalog\Api\BasePriceStorageInterface
{
    /**
     * Attribute code.
     *
     * @var string
     */
    private $attributeCode = 'price';

    /**
     * @var PricePersistence
     */
    private $pricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\BasePriceInterfaceFactory
     */
    private $basePriceInterfaceFactory;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Price type allowed.
     *
     * @var int
     */
    private $priceTypeAllowed = 1;

    /**
     * Allowed product types.
     *
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @var PricePersistenceFactory
     */
    private $pricePersistenceFactory;

    /**
     * BasePriceStorage constructor.
     *
     * @param PricePersistenceFactory $pricePersistenceFactory
     * @param \Magento\Catalog\Api\Data\BasePriceInterfaceFactory $basePriceInterfaceFactory
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $allowedProductTypes
     */
    public function __construct(
        PricePersistenceFactory $pricePersistenceFactory,
        \Magento\Catalog\Api\Data\BasePriceInterfaceFactory $basePriceInterfaceFactory,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $allowedProductTypes = []
    ) {
        $this->pricePersistenceFactory = $pricePersistenceFactory;
        $this->basePriceInterfaceFactory = $basePriceInterfaceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $skus)
    {
        $this->validateSkus($skus);
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
     */
    public function update(array $prices)
    {
        $this->validate($prices);
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

        return true;
    }

    /**
     * Get price persistence.
     *
     * @return PricePersistence
     */
    private function getPricePersistence()
    {
        if (!$this->pricePersistence) {
            $this->pricePersistence = $this->pricePersistenceFactory->create(['attributeCode' => $this->attributeCode]);
        }

        return $this->pricePersistence;
    }

    /**
     * Validate SKU, check product types and skip not existing products.
     *
     * @param array $skus
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function validateSkus(array $skus)
    {
        $idsBySku = $this->productIdLocator->retrieveProductIdsBySkus($skus);
        $skuDiff = array_diff($skus, array_keys($idsBySku));

        foreach ($idsBySku as $sku => $ids) {
            foreach ($ids as $type) {
                if (!in_array($type, $this->allowedProductTypes)
                    || (
                        $type == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                        && $this->productRepository->get($sku)->getPriceType() != $this->priceTypeAllowed
                    )
                ) {
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
     * Validate that prices have appropriate values.
     *
     * @param array $prices
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function validate(array $prices)
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

        foreach ($prices as $price) {
            if (null === $price->getPrice() || $price->getPrice() < 0) {
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
            $this->storeRepository->getById($price->getStoreId());
        }
    }
}
