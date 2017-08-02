<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Plugin\Checkout\CustomerData;

/**
 * Class \Magento\Weee\Plugin\Checkout\CustomerData\Cart
 *
 * @since 2.0.0
 */
class Cart extends \Magento\Tax\Plugin\Checkout\CustomerData\Cart
{
    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     * @since 2.0.0
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     * @since 2.0.0
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     * @since 2.0.0
     */
    protected $itemWeePriceRenderer;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
     * @param \Magento\Weee\Block\Item\Price\Renderer $itemWeePriceRenderer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer,
        \Magento\Weee\Block\Item\Price\Renderer $itemWeePriceRenderer
    ) {
        parent::__construct($checkoutSession, $checkoutHelper, $itemPriceRenderer);
        $this->itemPriceRenderer = $itemWeePriceRenderer;
    }
}
