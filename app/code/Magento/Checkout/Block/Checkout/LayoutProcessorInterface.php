<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
 * @since 100.0.2
 */
interface LayoutProcessorInterface
{
    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout);
}
