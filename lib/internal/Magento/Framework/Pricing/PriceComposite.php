<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing;

use Magento\Framework\Pricing\Price\Factory as PriceFactory;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Composite price model
 * @since 2.0.0
 */
class PriceComposite
{
    /**
     * @var PriceFactory
     * @since 2.0.0
     */
    protected $priceFactory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $metadata;

    /**
     * @param PriceFactory $priceFactory
     * @param array $metadata
     * @since 2.0.0
     */
    public function __construct(PriceFactory $priceFactory, array $metadata = [])
    {
        $this->priceFactory = $priceFactory;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getPriceCodes()
    {
        return array_keys($this->metadata);
    }

    /**
     * Returns metadata for prices
     *
     * @return array
     * @since 2.0.0
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param SaleableInterface $salableItem
     * @param string $priceCode
     * @param float $quantity
     * @return PriceInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function createPriceObject(SaleableInterface $salableItem, $priceCode, $quantity)
    {
        if (!isset($this->metadata[$priceCode])) {
            throw new \InvalidArgumentException($priceCode . ' is not registered in prices list');
        }
        $className = $this->metadata[$priceCode]['class'];
        return $this->priceFactory->create($salableItem, $className, $quantity);
    }
}
