<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Pricing\Price;

/**
 * Option price interface
 * @api
 * @since 2.0.0
 */
interface BundleOptionPriceInterface
{
    /**
     * Return calculated options
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptions();

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    public function getOptionSelectionAmount($selection);
}
