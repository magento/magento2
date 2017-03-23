<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
