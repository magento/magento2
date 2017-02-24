<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class IframeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        Config::METHOD_PAYFLOWADVANCED,
        Config::METHOD_PAYFLOWLINK,
        Config::METHOD_HOSTEDPRO,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
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
     */
    protected function getFrameActionUrl($code)
    {
        $url = '';
        switch($code) {
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
