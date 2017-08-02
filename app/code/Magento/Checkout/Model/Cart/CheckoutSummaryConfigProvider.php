<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\Cart;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CheckoutSummaryConfigProvider provides configuration for checkout summary block
 * @since 2.2.0
 */
class CheckoutSummaryConfigProvider implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getConfig()
    {
        return [
            'maxCartItemsToDisplay' => $this->getMaxCartItemsToDisplay(),
            'cartUrl' => $this->urlBuilder->getUrl('checkout/cart')
        ];
    }

    /**
     * Returns maximum cart items to display
     * This setting regulates how many items will be displayed in checkout summary block
     *
     * @return int
     * @since 2.2.0
     */
    private function getMaxCartItemsToDisplay()
    {
        return (int)$this->scopeConfig->getValue(
            'checkout/options/max_items_display_count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
