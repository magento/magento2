<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Pricing\Price;

/**
 * Option price interface
 * @api
 */
interface BundleOptionPriceInterface
{
    /**
     * Return calculated options
     *
     * @return array
     */
    public function getOptions();

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getOptionSelectionAmount($selection);
}
