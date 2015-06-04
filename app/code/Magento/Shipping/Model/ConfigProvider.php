<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'shippingPolicy' => [
                'isEnabled' => $this->scopeConfig->isSetFlag(
                    'shipping/shipping_policy/enable_shipping_policy',
                    ScopeInterface::SCOPE_STORE
                ),
                'shippingPolicyContent' => $this->scopeConfig->getValue(
                    'shipping/shipping_policy/shipping_policy_content',
                    ScopeInterface::SCOPE_STORE
                )
            ]
        ];
    }
}
