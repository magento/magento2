<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Paypal\Helper\Data as PaypalHelper;

/**
 * Class ExpressConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressConfigProvider implements ConfigProviderInterface
{
    const IN_CONTEXT_BUTTON_ID = 'paypal-express-in-context-button';

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
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var SmartButtonConfig
     */
    private $smartButtonConfig;

    /**
     * ExpressConfigProvider constructor.
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaypalHelper $paypalHelper
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     * @param SmartButtonConfig|null $smartButtonConfig
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ConfigFactory $configFactory,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaypalHelper $paypalHelper,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder,
        SmartButtonConfig $smartButtonConfig
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->currentCustomer = $currentCustomer;
        $this->paypalHelper = $paypalHelper;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;
        $this->smartButtonConfig = $smartButtonConfig;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $locale = $this->localeResolver->getLocale();

        $config = [
            'payment' => [
                'paypalExpress' => [
                    'paymentAcceptanceMarkHref' => $this->config->getPaymentMarkWhatIsPaypalUrl(
                        $this->localeResolver
                    ),
                    'paymentAcceptanceMarkSrc' => $this->config->getPaymentMarkImageUrl(
                        $locale
                    ),
                    'isContextCheckout' => false,
                    'inContextConfig' => []
                ]
            ]
        ];

        $isInContext = $this->isInContextCheckout();
        if ($isInContext) {
            $config['payment']['paypalExpress']['isContextCheckout'] = $isInContext;
            $config['payment']['paypalExpress']['inContextConfig'] = [
                'inContextId' => self::IN_CONTEXT_BUTTON_ID,
                'merchantId' => $this->config->getValue('merchant_id'),
            ];
            $clientConfig = [
                'button' => [
                    self::IN_CONTEXT_BUTTON_ID
                ],
                'getTokenUrl' => $this->urlBuilder->getUrl('paypal/express/getTokenData'),
                'onAuthorizeUrl' => $this->urlBuilder->getUrl('paypal/express/onAuthorization'),
                'onCancelUrl' => $this->urlBuilder->getUrl('paypal/express/cancel')
            ];
            $clientConfig = array_replace_recursive($clientConfig, $this->smartButtonConfig->getConfig('checkout'));
            $config['payment']['paypalExpress']['inContextConfig']['clientConfig'] = $clientConfig;
        }

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
     * Return setting value for in context checkout
     *
     * @return bool
     */
    protected function isInContextCheckout()
    {
        $this->config->setMethod(Config::METHOD_EXPRESS);

        return (bool)(int) $this->config->getValue('in_context');
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
