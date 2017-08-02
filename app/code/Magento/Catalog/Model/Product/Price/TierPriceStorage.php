<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;

/**
 * Tier price storage.
 * @since 2.2.0
 */
class TierPriceStorage implements \Magento\Catalog\Api\TierPriceStorageInterface
{
    /**
     * Tier price resource model.
     *
     * @var TierPricePersistence
     * @since 2.2.0
     */
    private $tierPricePersistence;

    /**
     * Tier price validator.
     *
     * @var \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator
     * @since 2.2.0
     */
    private $tierPriceValidator;

    /**
     * Tier price builder.
     *
     * @var TierPriceFactory
     * @since 2.2.0
     */
    private $tierPriceFactory;

    /**
     * Price indexer.
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Price
     * @since 2.2.0
     */
    private $priceIndexer;

    /**
     * Product ID locator.
     *
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     * @since 2.2.0
     */
    private $productIdLocator;

    /**
     * Page cache config.
     *
     * @var \Magento\PageCache\Model\Config
     * @since 2.2.0
     */
    private $config;

    /**
     * Cache type list.
     *
     * @var \Magento\Framework\App\Cache\TypeListInterface
     * @since 2.2.0
     */
    private $typeList;

    /**
     * Indexer chunk value.
     *
     * @var int
     * @since 2.2.0
     */
    private $indexerChunkValue = 500;

    /**
     * @param TierPricePersistence $tierPricePersistence
     * @param \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator $tierPriceValidator
     * @param TierPriceFactory $tierPriceFactory
     * @param \Magento\Catalog\Model\Indexer\Product\Price $priceIndexer
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @since 2.2.0
     */
    public function __construct(
        TierPricePersistence $tierPricePersistence,
        \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator $tierPriceValidator,
        TierPriceFactory $tierPriceFactory,
        \Magento\Catalog\Model\Indexer\Product\Price $priceIndexer,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\Cache\TypeListInterface $typeList
    ) {
        $this->tierPricePersistence = $tierPricePersistence;
        $this->tierPriceValidator = $tierPriceValidator;
        $this->tierPriceFactory = $tierPriceFactory;
        $this->priceIndexer = $priceIndexer;
        $this->productIdLocator = $productIdLocator;
        $this->config = $config;
        $this->typeList = $typeList;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function get(array $skus)
    {
        $skus = $this->tierPriceValidator->validateSkus($skus);

        return $this->getExistingPrices($skus);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function update(array $prices)
    {
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $result = $this->tierPriceValidator->retrieveValidationResult($prices, $this->getExistingPrices($skus, true));
        $prices = $this->removeIncorrectPrices($prices, $result->getFailedRowIds());
        $formattedPrices = $this->retrieveFormattedPrices($prices);
        $this->tierPricePersistence->update($formattedPrices);
        $this->reindexPrices($affectedIds);
        $this->invalidateFullPageCache();

        return $result->getFailedItems();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function replace(array $prices)
    {
        $result = $this->tierPriceValidator->retrieveValidationResult($prices);
        $prices = $this->removeIncorrectPrices($prices, $result->getFailedRowIds());
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $formattedPrices = $this->retrieveFormattedPrices($prices);
        $this->tierPricePersistence->replace($formattedPrices, $affectedIds);
        $this->reindexPrices($affectedIds);
        $this->invalidateFullPageCache();

        return $result->getFailedItems();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function delete(array $prices)
    {
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $result = $this->tierPriceValidator->retrieveValidationResult($prices);
        $prices = $this->removeIncorrectPrices($prices, $result->getFailedRowIds());
        $priceIds = $this->retrieveAffectedPriceIds($prices);
        $this->tierPricePersistence->delete($priceIds);
        $this->reindexPrices($affectedIds);
        $this->invalidateFullPageCache();

        return $result->getFailedItems();
    }

    /**
     * Get existing prices by SKUs.
     *
     * @param array $skus
     * @param bool $groupBySku [optional]
     * @return array
     * @since 2.2.0
     */
    private function getExistingPrices(array $skus, $groupBySku = false)
    {
        $ids = $this->retrieveAffectedIds($skus);
        $rawPrices = $this->tierPricePersistence->get($ids);
        $prices = [];

        foreach ($rawPrices as $rawPrice) {
            $sku = $this->retrieveSkuById($rawPrice[$this->tierPricePersistence->getEntityLinkField()], $skus);
            $price = $this->tierPriceFactory->create($rawPrice, $sku);
            if ($groupBySku) {
                $prices[$sku][] = $price;
            } else {
                $prices[] = $price;
            }
        }

        return $prices;
    }

    /**
     * Retrieve formatted prices.
     *
     * @param array $prices
     * @return array
     * @since 2.2.0
     */
    private function retrieveFormattedPrices(array $prices)
    {
        $formattedPrices = [];

        foreach ($prices as $price) {
            $idsBySku = $this->productIdLocator->retrieveProductIdsBySkus([$price->getSku()]);
            $ids = array_keys($idsBySku[$price->getSku()]);
            foreach ($ids as $id) {
                $formattedPrices[] = $this->tierPriceFactory->createSkeleton($price, $id);
            }
        }

        return $formattedPrices;
    }

    /**
     * Retrieve affected product IDs for prices.
     *
     * @param TierPriceInterface[] $prices
     * @return array
     * @since 2.2.0
     */
    private function retrieveAffectedProductIdsForPrices(array $prices)
    {
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );

        return $this->retrieveAffectedIds($skus);
    }

    /**
     * Retrieve affected product IDs.
     *
     * @param array $skus
     * @return array
     * @since 2.2.0
     */
    private function retrieveAffectedIds(array $skus)
    {
        $affectedIds = [];

        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $productId) {
            $affectedIds = array_merge($affectedIds, array_keys($productId));
        }

        return array_unique($affectedIds);
    }

    /**
     * Retrieve affected price IDs.
     *
     * @param array $prices
     * @return array
     * @since 2.2.0
     */
    private function retrieveAffectedPriceIds(array $prices)
    {
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $formattedPrices = $this->retrieveFormattedPrices($prices);
        $existingPrices = $this->tierPricePersistence->get($affectedIds);
        $priceIds = [];

        foreach ($formattedPrices as $price) {
            $priceIds[] = $this->retrievePriceId($price, $existingPrices);
        }

        return $priceIds;
    }

    /**
     * Look through provided price in list of existing prices and retrieve it's Id.
     *
     * @param array $price
     * @param array $existingPrices
     * @return int|null
     * @since 2.2.0
     */
    private function retrievePriceId(array $price, array $existingPrices)
    {
        $linkField = $this->tierPricePersistence->getEntityLinkField();

        foreach ($existingPrices as $existingPrice) {
            if ($existingPrice['all_groups'] == $price['all_groups']
                && $existingPrice['customer_group_id'] == $price['customer_group_id']
                && $existingPrice['qty'] == $price['qty']
                && $this->isCorrectPriceValue($existingPrice, $price)
                && $existingPrice[$linkField] == $price[$linkField]
            ) {
                return $existingPrice['value_id'];
            }
        }

        return null;
    }

    /**
     * Check that price value or price percentage value is not equal to 0 and is not similar with existing value.
     *
     * @param array $existingPrice
     * @param array $price
     * @return bool
     * @since 2.2.0
     */
    private function isCorrectPriceValue(array $existingPrice, array $price)
    {
        return ($existingPrice['value'] != 0 && $existingPrice['value'] == $price['value'])
            || ($existingPrice['percentage_value'] !== null
                && $existingPrice['percentage_value'] == $price['percentage_value']);
    }

    /**
     * Retrieve SKU by product ID.
     *
     * @param int $id
     * @param array $skus
     * @return string|null
     * @since 2.2.0
     */
    private function retrieveSkuById($id, $skus)
    {
        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $sku => $ids) {
            if (isset($ids[$id])) {
                return $sku;
            }
        }

        return null;
    }

    /**
     * Invalidate full page cache.
     *
     * @return void
     * @since 2.2.0
     */
    private function invalidateFullPageCache()
    {
        if ($this->config->isEnabled()) {
            $this->typeList->invalidate('full_page');
        }
    }

    /**
     * Reindex prices.
     *
     * @param array $ids
     * @return void
     * @since 2.2.0
     */
    private function reindexPrices(array $ids)
    {
        foreach (array_chunk($ids, $this->indexerChunkValue) as $affectedIds) {
            $this->priceIndexer->execute($affectedIds);
        }
    }

    /**
     * Remove prices from price list by id list.
     *
     * @param array $prices
     * @param array $ids
     * @return array
     * @since 2.2.0
     */
    private function removeIncorrectPrices(array $prices, array $ids)
    {
        foreach ($ids as $id) {
            unset($prices[$id]);
        }

        return $prices;
    }
}
