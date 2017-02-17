<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Render;

/**
 * Responsible for displaying tier price box on configurable product page.
 *
 * @package Magento\ConfigurableProduct\Pricing\Render
 */
class TierPriceBox extends FinalPriceBox
{
    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        // Hide tier price block in case of MSRP.
        if (!$this->isMsrpPriceApplicable()) {
            return parent::toHtml();
        }
    }
}
