<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Checkout;

use Magento\Checkout\Model\Layout\AbstractTotalsProcessor;

/**
 * Class \Magento\Checkout\Block\Checkout\TotalsProcessor
 *
 * @since 2.0.0
 */
class TotalsProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
