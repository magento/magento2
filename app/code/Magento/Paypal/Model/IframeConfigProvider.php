<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class \Magento\Paypal\Model\IframeConfigProvider
 *
 * @since 2.0.0
 */
class IframeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $methodCodes = [
        Config::METHOD_PAYFLOWADVANCED,
        Config::METHOD_PAYFLOWLINK,
        Config::METHOD_HOSTEDPRO,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     * @since 2.0.0
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     * @since 2.0.0
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     * @since 2.0.0
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'paypalIframe' => [],
            ],
        ];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['paypalIframe']['actionUrl'][$code] = $this->getFrameActionUrl($code);
            }
        }

        return $config;
    }

    /**
     * Get frame action URL
     *
     * @param string $code
     * @return string
     * @since 2.0.0
     */
    protected function getFrameActionUrl($code)
    {
        $url = '';
        switch ($code) {
            case Config::METHOD_PAYFLOWADVANCED:
                $url = $this->urlBuilder->getUrl('paypal/payflowadvanced/form', ['_secure' => true]);
                break;
            case Config::METHOD_PAYFLOWLINK:
                $url = $this->urlBuilder->getUrl('paypal/payflow/form', ['_secure' => true]);
                break;
            case Config::METHOD_HOSTEDPRO:
                $url = $this->urlBuilder->getUrl('paypal/hostedpro/redirect', ['_secure' => true]);
                break;
        }

        return $url;
    }
}
