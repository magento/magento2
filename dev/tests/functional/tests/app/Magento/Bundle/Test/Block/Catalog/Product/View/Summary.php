<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View;

use Magento\Bundle\Test\Block\Catalog\Product\View\Summary\ConfiguredPrice;
use Magento\Catalog\Test\Block\Product\View;
use Magento\Mtf\Client\ElementInterface;

/**
 * Bundle Summary block.
 */
class Summary extends View
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
        /** @var ElementInterface $element */
        $element = $this->_rootElement->find($this->configuredPriceBlockSelector);

        return $this->blockFactory->create(
            ConfiguredPrice::class,
            ['element' => $element]
        );
    }
}
