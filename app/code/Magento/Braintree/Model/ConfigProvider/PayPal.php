<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\ConfigProvider;

use Magento\Braintree\Model\Adapter\BraintreeClientToken;
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
     * Braintree Client Token
     *
     * @var \Magento\Framework\Url
     */
    protected $braintreeClientToken;

    /**
     * @param PayPalConfig $config
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Braintree\Model\Adapter\BraintreeClientToken
     */
    public function __construct(
        PayPalConfig $config,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Braintree\Model\Adapter\BraintreeClientToken $braintreeClientToken
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->braintreeClientToken = $braintreeClientToken;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        if (!$this->config->isActive()) {
            return [];
        }
        $clientToken = $this->braintreeClientToken->generate();

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
