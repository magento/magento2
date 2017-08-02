<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Checkout\Model\Layout\AbstractTotalsProcessor;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class \Magento\Checkout\Block\Cart\CartTotalsProcessor
 *
 * @since 2.0.0
 */
class CartTotalsProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function process($jsLayout)
    {
        $totals = $jsLayout['components']['block-totals']['children'];
        $jsLayout['components']['block-totals']['children'] = $this->sortTotals($totals);
        return $jsLayout;
    }
}
