<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const PAYPAL_CODE = 'braintree_paypal';

    const PAYPAL_VAULT_CODE = 'braintree_paypal_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    public function __construct(Config $config, ResolverInterface $resolver)
    {
        $this->config = $config;
        $this->resolver = $resolver;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::PAYPAL_CODE => [
                    'isActive' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'locale' => strtolower($this->resolver->getLocale()),
                    'paymentAcceptanceMarkSrc' =>
                        'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                    'vaultCode' => self::PAYPAL_VAULT_CODE,
                    'skipOrderReview' => $this->config->isSkipOrderReview(),
                    'paymentIcon' => $this->config->getPayPalIcon(),
                ]
            ]
        ];
    }
}
