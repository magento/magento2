<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Render;

/**
 * Responsible for displaying tier price box on configurable product page.
 *
 * @package Magento\ConfigurableProduct\Pricing\Render
 * @since 2.2.0
 */
class TierPriceBox extends FinalPriceBox
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function toHtml()
    {
        // Hide tier price block in case of MSRP.
        if (!$this->isMsrpPriceApplicable()) {
            return parent::toHtml();
        }
    }
}
