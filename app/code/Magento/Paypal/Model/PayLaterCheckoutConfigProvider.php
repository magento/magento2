<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Provides configuration values for PayPal PayLater on the checkout page
 */
class PayLaterCheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var PayLaterConfig
     */
    private $payLaterConfig;

    /**
     * @var SdkUrl
     */
    private $sdkUrl;

    /**
     * @param PayLaterConfig $payLaterConfig
     * @param SdkUrl $sdkUrl
     */
    public function __construct(PayLaterConfig $payLaterConfig, SdkUrl $sdkUrl)
    {
        $this->payLaterConfig = $payLaterConfig;
        $this->sdkUrl = $sdkUrl;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $attributes = $this->payLaterConfig->getStyleConfig(PayLaterConfig::CHECKOUT_PAYMENT_PLACEMENT);
        $attributes['data-pp-placement'] = PayLaterConfig::CHECKOUT_PAYMENT_PLACEMENT;

        $config['payment']['paypalPayLater']['enabled'] = $this->payLaterConfig->isEnabled(
            PayLaterConfig::CHECKOUT_PAYMENT_PLACEMENT
        );
        $config['payment']['paypalPayLater']['config'] = [
            'sdkUrl' => $this->sdkUrl->getUrl(),
            'attributes' => $attributes
        ];

        return $config;
    }
}
