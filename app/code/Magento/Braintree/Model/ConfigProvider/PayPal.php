<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Braintree\Model\Config\PayPal as PayPalConfig;
use Magento\Braintree\Model\PaymentMethod\PayPal as PayPalPaymentMethod;

class PayPal implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        PayPalPaymentMethod::METHOD_CODE,
    ];

    /**
     * @var PayPalConfig
     */
    protected $config;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param PayPalConfig $config
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        PayPalConfig $config,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        if (!$this->config->isActive()) {
            return [];
        }
        $clientToken = $this->config->getClientToken();

        $config = [
            'payment' => [
                'braintree_paypal' => [
                    'clientToken' => $clientToken,
                    'locale' => $this->localeResolver->getLocale(),
                    'merchantDisplayName' => $this->config->getMerchantNameOverride(),
                ],
            ]
        ];

        return $config;
    }
}
