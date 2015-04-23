<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;

/**
 * Configuration provider for GiftMessage rendering on "Shipping Method" step of checkout.
 */
class GiftMessageConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfiguration;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        $this->scopeConfiguration = $context->getScopeConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $configuration = [];
        $orderLevelGiftMessageConfiguration = (bool)$this->scopeConfiguration->getValue(
            GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $itemLevelGiftMessageConfiguration = (bool)$this->scopeConfiguration->getValue(
            GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($orderLevelGiftMessageConfiguration) {
            $configuration['isOrderLevelGiftOptionsEnabled'] = true;
            $configuration['giftMessage']['orderLevel'] = true;
        }
        if ($itemLevelGiftMessageConfiguration) {
            $configuration['isItemLevelGiftOptionsEnabled'] = true;
            $configuration['giftMessage']['itemLevel'] = true;
        }
        return $configuration;
    }
}
