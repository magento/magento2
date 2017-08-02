<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Plugin\Checkout\CustomerData;

/**
 * Class \Magento\Weee\Plugin\Checkout\CustomerData\Cart
 *
 */
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
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $itemWeePriceRenderer;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
     * @param \Magento\Weee\Block\Item\Price\Renderer $itemWeePriceRenderer
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
