<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Block\Checkout;

use Magento\Checkout\Model\Layout\AbstractTotalsProcessor;

class TotalsProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $totals = $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
        ['children']['totals']['children'];
        $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
        ['children']['totals']['children'] = $this->sortTotals($totals);
        return $jsLayout;
    }
}
