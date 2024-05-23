<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\PayLater;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Paypal\Model\PayLaterConfig;
use Magento\Paypal\Model\SdkUrl;
use Magento\Paypal\Model\Config as PaypalConfig;

/**
 * PayLater Layout Processor
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * Checkout payment page placement
     */
    private const PLACEMENT = 'payment';

    /**
     * @var PayLaterConfig
     */
    private $payLaterConfig;

    /**
     * @var SdkUrl
     */
    private $sdkUrl;

    /**
     * @var PaypalConfig
     */
    private $paypalConfig;

    /**
     * @param PayLaterConfig $payLaterConfig
     * @param SdkUrl $sdkUrl
     * @param PaypalConfig $paypalConfig
     */
    public function __construct(
        PayLaterConfig $payLaterConfig,
        SdkUrl $sdkUrl,
        PaypalConfig $paypalConfig
    ) {
        $this->payLaterConfig = $payLaterConfig;
        $this->sdkUrl = $sdkUrl;
        $this->paypalConfig = $paypalConfig;
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if (!$this->payLaterConfig->isEnabled(PayLaterConfig::CHECKOUT_PAYMENT_PLACEMENT)) {
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['payments-list']['children']['paypal-method-extra-content']
                ['children']['paylater-place-order']);

            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children']['paypal-method-extra-content']['children']
            ['paylater-place-order'])
        ) {
            $payLaterPlaceOrder = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children']['paypal-method-extra-content']['children']
            ['paylater-place-order'];

            $componentConfig = $payLaterPlaceOrder['config'] ?? [];
            $defaultConfig = [
                'sdkUrl' => $this->sdkUrl->getUrl(),
                'displayAmount' => true,
                'amountComponentConfig' => [
                    'component' => 'Magento_Paypal/js/view/amountProviders/checkout'
                ]
            ];
            $config = array_replace($defaultConfig, $componentConfig);
            $displayAmount = $config['displayAmount'] ?? false;
            $config['displayAmount'] = !$displayAmount || $this->payLaterConfig->isPPBillingAgreementEnabled()
                ? false : true;
            $config['dataAttributes'] = [
                'data-partner-attribution-id' => $this->paypalConfig->getBuildNotationCode(),
                'data-csp-nonce' => $this->paypalConfig->getCspNonce(),
            ];

            $attributes = $this->payLaterConfig->getSectionConfig(
                PayLaterConfig::CHECKOUT_PAYMENT_PLACEMENT,
                PayLaterConfig::CONFIG_KEY_STYLE
            );
            $attributes['data-pp-placement'] = self::PLACEMENT;

            $componentAttributes = $payLaterPlaceOrder['config']['attributes'] ?? [];
            $config['attributes'] = array_replace($attributes, $componentAttributes);

            $payLaterPlaceOrder['config'] = $config;
        }

        return $jsLayout;
    }
}
