<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Plugin\Checkout\CustomerData;

class Cart extends \Magento\Tax\Plugin\Checkout\CustomerData\Cart
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Weee\Block\Item\Price\Renderer $itemPriceRenderer
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Weee\Block\Item\Price\Renderer $itemPriceRenderer
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
    }
}
