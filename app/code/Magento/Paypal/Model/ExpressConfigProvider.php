<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Paypal\Helper\Data as PaypalHelper;

class ExpressConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var PaypalHelper
     */
    protected $paypalHelper;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        Config::METHOD_WPP_BML,
        Config::METHOD_WPP_PE_EXPRESS,
        Config::METHOD_WPP_EXPRESS,
        Config::METHOD_WPP_PE_BML
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
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaypalHelper $paypalHelper
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        ConfigFactory $configFactory,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaypalHelper $paypalHelper,
        PaymentHelper $paymentHelper
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->currentCustomer = $currentCustomer;
        $this->paypalHelper = $paypalHelper;
        $this->paymentHelper = $paymentHelper;

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
                'paypalExpress' => [
                    'paymentAcceptanceMarkHref' => $this->config->getPaymentMarkWhatIsPaypalUrl(
                        $this->localeResolver
                    ),
                    'paymentAcceptanceMarkSrc' => $this->config->getPaymentMarkImageUrl(
                        $this->localeResolver->getLocale()
                    ),
                ]
            ]
        ];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['paypalExpress']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
                $config['payment']['paypalExpress']['billingAgreementCode'][$code] =
                    $this->getBillingAgreementCode($code);
            }
        }
        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }

    /**
     * Return billing agreement code for method
     *
     * @param string $code
     * @return null|string
     */
    protected function getBillingAgreementCode($code)
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $this->config->setMethod($code);
        return $this->paypalHelper->shouldAskToCreateBillingAgreement($this->config, $customerId)
            ? Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT : null;
    }
}
