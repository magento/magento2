<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Product\Plugin;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Catalog\Model\ProductIdLocatorInterface;

/**
 * UpdateQuote Plugin Class
 */
class UpdateQuote
{

    /**
     * Quote Resource
     *
     * @var Quote
     */
    private $resource;

    /**
     * Product ID locator.
     *
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * Construct Method for updateQuote Plugin
     *
     * @param Quote $resource
     * @param ProductIdLocatorInterface $productIdLocator
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote $resource,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
    ) {
        $this->resource = $resource;
        $this->productIdLocator = $productIdLocator;
    }

    /**
     * Update the quote trigger_recollect column is 1 when product price is changed through API.
     *
     * @param TierPriceStorageInterface $subject
     * @param $result
     * @param $prices
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdate(
        TierPriceStorageInterface $subject,
        $result,
        $prices
    ): array {
        $this->resource->markQuotesRecollect($this->retrieveAffectedProductIdsForPrices($prices));
        return $result;

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
}
