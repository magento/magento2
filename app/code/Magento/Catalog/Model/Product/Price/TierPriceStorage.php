<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Customer\Model\ResourceModel\Group\GetCustomerGroupCodesByIds;

class TierPriceStorage implements TierPriceStorageInterface
{
    /**
     * Tier price resource model.
     *
     * @var TierPricePersistence
     */
    private $tierPricePersistence;

    /**
     * @var TierPriceValidator
     */
    private $tierPriceValidator;

    /**
     * Tier price builder.
     *
     * @var TierPriceFactory
     */
    private $tierPriceFactory;

    /**
     * @var PriceIndexerProcessor
     */
    private $priceIndexProcessor;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var GetCustomerGroupCodesByIds
     */
    private $getCustomerGroupCodesByIds;

    /**
     * @param TierPricePersistence $tierPricePersistence
     * @param TierPriceValidator $tierPriceValidator
     * @param TierPriceFactory $tierPriceFactory
     * @param PriceIndexerProcessor $priceIndexProcessor
     * @param ProductIdLocatorInterface $productIdLocator
     * @param GetCustomerGroupCodesByIds $getCustomerGroupCodesByIds
     */
    public function __construct(
        TierPricePersistence $tierPricePersistence,
        TierPriceValidator $tierPriceValidator,
        TierPriceFactory $tierPriceFactory,
        PriceIndexerProcessor $priceIndexProcessor,
        ProductIdLocatorInterface $productIdLocator,
        GetCustomerGroupCodesByIds $getCustomerGroupCodesByIds
    ) {
        $this->tierPricePersistence = $tierPricePersistence;
        $this->tierPriceValidator = $tierPriceValidator;
        $this->tierPriceFactory = $tierPriceFactory;
        $this->priceIndexProcessor = $priceIndexProcessor;
        $this->productIdLocator = $productIdLocator;
        $this->getCustomerGroupCodesByIds = $getCustomerGroupCodesByIds;
    }

    /**
     * @inheritdoc
     */
    public function get(array $skus)
    {
        $skus = $this->tierPriceValidator->validateSkus($skus);

        return $this->getExistingPrices($skus);
    }

    /**
     * @inheritdoc
     */
    public function update(array $prices)
    {
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $skus = array_unique(
            array_map(
                function (TierPriceInterface $price) {
                    return $price->getSku();
                },
                $prices
            )
        );
        $result = $this->tierPriceValidator->retrieveValidationResult($prices, $this->getExistingPrices($skus, true));
        $prices = $this->removeIncorrectPrices($prices, $result->getFailedRowIds());
        $formattedPrices = $this->retrieveFormattedPrices($prices);
        $this->tierPricePersistence->update($formattedPrices);
        $this->reindexPrices($affectedIds);

        return $result->getFailedItems();
    }

    /**
     * @inheritdoc
     */
    public function replace(array $prices)
    {
        $result = $this->tierPriceValidator->retrieveValidationResult($prices);
        $prices = $this->removeIncorrectPrices($prices, $result->getFailedRowIds());
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $formattedPrices = $this->retrieveFormattedPrices($prices);
        $this->tierPricePersistence->replace($formattedPrices, $affectedIds);
        $this->reindexPrices($affectedIds);

        return $result->getFailedItems();
    }

    /**
     * @inheritdoc
     */
    public function delete(array $prices)
    {
        $affectedIds = $this->retrieveAffectedProductIdsForPrices($prices);
        $result = $this->tierPriceValidator->retrieveValidationResult($prices);
        $prices = $this->removeIncorrectPrices($prices, $result->getFailedRowIds());
        $priceIds = $this->retrieveAffectedPriceIds($prices);
        $this->tierPricePersistence->delete($priceIds);
        $this->reindexPrices($affectedIds);

        return $result->getFailedItems();
    }

    /**
     * Get existing prices by SKUs.
     *
     * @param array $skus
     * @param bool $groupBySku [optional]
     * @return array
     */
    private function getExistingPrices(array $skus, bool $groupBySku = false): array
    {
        $ids = $this->retrieveAffectedIds($skus);
        $rawPrices = $this->tierPricePersistence->get($ids);
        $prices = [];
        if ($rawPrices) {
            $linkField = $this->tierPricePersistence->getEntityLinkField();
            $skuByIdLookup = $this->buildSkuByIdLookup($skus);
            $customerGroupCodesByIds = $this->getCustomerGroupCodesByIds->execute(
                array_column(
                    array_filter(
                        $rawPrices,
                        static function (array $row) {
                            return (int) $row['all_groups'] !== 1;
                        }
                    ),
                    'customer_group_id'
                ),
            );
            foreach ($rawPrices as $rawPrice) {
                $sku = $skuByIdLookup[$rawPrice[$linkField]];
                if (isset($customerGroupCodesByIds[$rawPrice['customer_group_id']])) {
                    $rawPrice['customer_group_code'] = $customerGroupCodesByIds[$rawPrice['customer_group_id']];
                }
                $price = $this->tierPriceFactory->create($rawPrice, $sku);
                if ($groupBySku) {
                    $prices[$sku][] = $price;
                } else {
                    $prices[] = $price;
                }
            }
        }

        return $prices;
    }

    /**
     * Retrieve formatted prices.
     *
     * @param array $prices
     * @return array
     */
    private function retrieveFormattedPrices(array $prices): array
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
     */
    private function retrieveAffectedProductIdsForPrices(array $prices): array
    {
        $skus = array_unique(
            array_map(
                function (TierPriceInterface $price) {
                    return $price->getSku();
                },
                $prices
            )
        );

        return $this->retrieveAffectedIds($skus);
    }

    /**
     * Retrieve affected product IDs.
     *
     * @param array $skus
     * @return array
     */
    private function retrieveAffectedIds(array $skus): array
    {
        $affectedIds = [];

        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $productId) {
            $affectedIds[] = array_keys($productId);
        }

        return array_unique(array_merge([], ...$affectedIds));
    }

    /**
     * Retrieve affected price IDs.
     *
     * @param array $prices
     * @return array
     */
    private function retrieveAffectedPriceIds(array $prices): array
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
     */
    private function retrievePriceId(array $price, array $existingPrices): ?int
    {
        $linkField = $this->tierPricePersistence->getEntityLinkField();

        foreach ($existingPrices as $existingPrice) {
            if ($existingPrice['all_groups'] == $price['all_groups']
                && $existingPrice['customer_group_id'] == $price['customer_group_id']
                && $existingPrice['qty'] == $price['qty']
                && $this->isCorrectPriceValue($existingPrice, $price)
                && $existingPrice[$linkField] == $price[$linkField]
            ) {
                return (int) $existingPrice['value_id'];
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
     */
    private function isCorrectPriceValue(array $existingPrice, array $price): bool
    {
        return ($existingPrice['value'] != 0 && $existingPrice['value'] == $price['value'])
            || ($existingPrice['percentage_value'] !== null
                && $existingPrice['percentage_value'] == $price['percentage_value']);
    }

    /**
     * Generate lookup to retrieve SKU by product ID.
     *
     * @param array $skus
     * @return array
     */
    private function buildSkuByIdLookup(array $skus): array
    {
        $lookup = [];
        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $sku => $ids) {
            foreach (array_keys($ids) as $id) {
                $lookup[$id] = $sku;
            }
        }

        return $lookup;
    }

    /**
     * Reindex prices.
     *
     * @param array $ids
     * @return void
     */
    private function reindexPrices(array $ids): void
    {
        if (!empty($ids)) {
            $this->priceIndexProcessor->reindexList($ids);
        }
    }

    /**
     * Remove prices from price list by id list.
     *
     * @param array $prices
     * @param array $ids
     * @return array
     */
    private function removeIncorrectPrices(array $prices, array $ids): array
    {
        foreach ($ids as $id) {
            unset($prices[$id]);
        }

        return $prices;
    }
}
