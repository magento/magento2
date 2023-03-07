<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Plugin\Checkout\CustomerData;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session;
use Magento\Tax\Block\Item\Price\Renderer as ItemPriceRenderer;
use Magento\Tax\Plugin\Checkout\CustomerData\Cart as CustomerDataCart;
use Magento\Weee\Block\Item\Price\Renderer as ItemWeeeRenderer;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Cart extends CustomerDataCart
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * @var ItemPriceRenderer
     */
    protected $itemPriceRenderer;

    /**
     * @param Session $checkoutSession
     * @param CheckoutHelper $checkoutHelper
     * @param ItemPriceRenderer $itemPriceRenderer
     * @param ItemWeeeRenderer $itemWeePriceRenderer
     */
    public function __construct(
        Session $checkoutSession,
        CheckoutHelper $checkoutHelper,
        ItemPriceRenderer $itemPriceRenderer,
        protected ItemWeeeRenderer $itemWeePriceRenderer
    ) {
        parent::__construct($checkoutSession, $checkoutHelper, $itemPriceRenderer);
    }
}
