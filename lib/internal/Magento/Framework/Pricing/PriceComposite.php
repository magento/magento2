<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\Factory as PriceFactory;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Composite price model
 */
class PriceComposite
{
    /**
     * @var PriceFactory
     */
    protected $priceFactory;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param PriceFactory $priceFactory
     * @param array $metadata
     */
    public function __construct(PriceFactory $priceFactory, array $metadata = [])
    {
        $this->priceFactory = $priceFactory;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getPriceCodes()
    {
        return array_keys($this->metadata);
    }

    /**
     * Returns metadata for prices
     *
     * @return array
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
