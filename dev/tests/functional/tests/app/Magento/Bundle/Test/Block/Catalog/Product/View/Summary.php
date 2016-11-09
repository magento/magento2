<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View;

use Magento\Bundle\Test\Block\Catalog\Product\View\Summary\ConfiguredPrice;

/**
 * Bundle Summary block.
 */
class Summary extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Configured Price block selector.
     *
     * @var string
     */
    private $configuredPriceBlockSelector = '.price-configured_price';

    /**
     * Get configured price block.
     *
     * @return ConfiguredPrice
     */
    public function getConfiguredPriceBlock()
    {
        return $this->blockFactory->create(
            ConfiguredPrice::class,
            ['element' => $this->_rootElement->find($this->configuredPriceBlockSelector)]
        );
    }
}
