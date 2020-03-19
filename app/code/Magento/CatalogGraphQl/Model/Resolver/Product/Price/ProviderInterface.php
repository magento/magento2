<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Price;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Provides product prices
 */
interface ProviderInterface
{
    /**
     * Get the product minimal final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface;

    /**
     * Get the product minimal regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface;

    /**
     * Get the product maximum final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface;

    /**
     * Get the product maximum final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface;

    /**
     * Get the product regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface;
}
