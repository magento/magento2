<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Configurable regular price interface
 * @api
 */
interface ConfigurableRegularPriceInterface extends BasePriceProviderInterface
{
    /**
     * Get max regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxRegularAmount();

    /**
     * Get min regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinRegularAmount();
}
