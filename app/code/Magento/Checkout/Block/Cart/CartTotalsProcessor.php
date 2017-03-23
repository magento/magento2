<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Checkout\Model\Layout\AbstractTotalsProcessor;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class CartTotalsProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $totals = $jsLayout['components']['block-totals']['children'];
        $jsLayout['components']['block-totals']['children'] = $this->sortTotals($totals);
        return $jsLayout;
    }
}
