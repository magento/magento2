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
 */
class CheckoutSummaryConfigProvider implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
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
     */
    private function getMaxCartItemsToDisplay()
    {
        return (int)$this->scopeConfig->getValue(
            'checkout/options/max_items_display_count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
