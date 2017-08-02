<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Checkout;

/**
 * Layout processor interface.
 *
 * Can be used to provide a custom logic for checkout JS layout preparation.
 *
 * @see \Magento\Checkout\Block\Onepage
 *
 * @api
 * @since 2.0.0
 */
interface LayoutProcessorInterface
{
    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     * @since 2.0.0
     */
    public function process($jsLayout);
}
