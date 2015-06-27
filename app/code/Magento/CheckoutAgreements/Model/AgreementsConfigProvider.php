<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration provider for GiftMessage rendering on "Shipping Method" step of checkout.
 */
class AgreementsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfiguration;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration)
    {
        $this->scopeConfiguration = $scopeConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if ($this->scopeConfiguration->isSetFlag(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return ['checkoutAgreementsEnabled' => true];
        } else {
            return [];
        }
    }
}
