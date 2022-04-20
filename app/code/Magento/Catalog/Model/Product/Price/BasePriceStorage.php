<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\BasePriceStorageInterface;
use Magento\Catalog\Api\Data\BasePriceInterface;
use Magento\Catalog\Api\Data\BasePriceInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;

/**
 * Base prices storage.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasePriceStorage implements BasePriceStorageInterface
{
    /**
     * Price attribute code.
     *
     * @var string
     */
    private $attributeCode = 'price';

    /**
     * @var PricePersistence
     */
    private $pricePersistence;

    /**
     * @var BasePriceInterfaceFactory
     */
    private $basePriceInterfaceFactory;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Result
     */
    private $validationResult;

    /**
     * @var PricePersistenceFactory
     */
    private $pricePersistenceFactory;

    /**
     * @var InvalidSkuProcessor
     */
    private $invalidSkuProcessor;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * Is price type allowed
     *
     * @var int
     */
    private $priceTypeAllowed = 1;

    /**
     * Array of allowed product types.
     *
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @param PricePersistenceFactory $pricePersistenceFactory
     * @param BasePriceInterfaceFactory $basePriceInterfaceFactory
     * @param ProductIdLocatorInterface $productIdLocator
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Result $validationResult
     * @param InvalidSkuProcessor $invalidSkuProcessor
     * @param array $allowedProductTypes [optional]
     * @param ProductAttributeRepositoryInterface|null $productAttributeRepository
     */
    public function __construct(
        PricePersistenceFactory $pricePersistenceFactory,
        BasePriceInterfaceFactory $basePriceInterfaceFactory,
        ProductIdLocatorInterface $productIdLocator,
        StoreRepositoryInterface $storeRepository,
        ProductRepositoryInterface $productRepository,
        Result $validationResult,
        InvalidSkuProcessor $invalidSkuProcessor,
        array $allowedProductTypes = [],
        ProductAttributeRepositoryInterface $productAttributeRepository = null
    ) {
        $this->pricePersistenceFactory = $pricePersistenceFactory;
        $this->basePriceInterfaceFactory = $basePriceInterfaceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->validationResult = $validationResult;
        $this->allowedProductTypes = $allowedProductTypes;
        $this->invalidSkuProcessor = $invalidSkuProcessor;
        $this->productAttributeRepository = $productAttributeRepository ?: ObjectManager::getInstance()
            ->get(ProductAttributeRepositoryInterface::class);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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

        $priceAttribute = $this->productAttributeRepository->get($this->attributeCode);

        if ($priceAttribute !== null && $priceAttribute->isScopeWebsite()) {
            $formattedPrices = $this->applyWebsitePrices($formattedPrices);
        }

        $this->getPricePersistence()->update($formattedPrices);

        return $this->validationResult->getFailedItems();
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
     * Retrieve valid prices that do not contain any errors.
     *
     * @param BasePriceInterface[] $prices
     * @return array
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
            } catch (NoSuchEntityException $e) {
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

    /**
     * If Catalog Price Mode is Website, price needs to be applied to all Store Views in this website.
     *
     * @param array $formattedPrices
     * @return array
     * @throws NoSuchEntityException
     */
    private function applyWebsitePrices($formattedPrices): array
    {
        foreach ($formattedPrices as $price) {
            if ($price['store_id'] == Store::DEFAULT_STORE_ID) {
                continue;
            }

            $storeIds = $this->storeRepository->getById($price['store_id'])->getWebsite()->getStoreIds();

            // Unset origin store view to get rid of duplicate
            unset($storeIds[$price['store_id']]);

            foreach ($storeIds as $storeId) {
                $price['store_id'] = (int)$storeId;
                $formattedPrices[] = $price;
            }
        }

        return $formattedPrices;
    }
}
