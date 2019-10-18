<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\DataProviders;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Provides additional data for bundle options
 */
class OptionPriceRenderer implements ArgumentInterface
{
    /**
     * Parent layout of the block
     *
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Format tier price string
     *
     * @param Product $selection
     * @param array $arguments
     * @return string
     */
    public function renderTierPrice(Product $selection, array $arguments = []): string
    {
        if (!array_key_exists('zone', $arguments)) {
            $arguments['zone'] = Render::ZONE_ITEM_OPTION;
        }

        $priceHtml = '';

        /** @var Render $priceRender */
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if ($priceRender !== false) {
            $priceHtml = $priceRender->render(
                TierPrice::PRICE_CODE,
                $selection,
                $arguments
            );
        }

        return $priceHtml;
    }
}
