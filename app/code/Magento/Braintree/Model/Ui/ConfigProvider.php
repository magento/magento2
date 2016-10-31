<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'braintree';

    /**
     * @deprecated
     */
    const PAYPAL_CODE = 'braintree_paypal';

    const CC_VAULT_CODE = 'braintree_cc_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * Constructor
     *
     * @param Config $config
     * @param PayPalConfig $payPalConfig;
     * @param BraintreeAdapter $adapter
     * @param ResolverInterface $localeResolver
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $config,
        PayPalConfig $payPalConfig,
        BraintreeAdapter $adapter,
        ResolverInterface $localeResolver
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isActive(),
                    'clientToken' => $this->getClientToken(),
                    'ccTypesMapper' => $this->config->getCctypesMapper(),
                    'sdkUrl' => $this->config->getSdkUrl(),
                    'countrySpecificCardTypes' => $this->config->getCountrySpecificCardTypeConfig(),
                    'availableCardTypes' => $this->config->getAvailableCardTypes(),
                    'useCvv' => $this->config->isCvvEnabled(),
                    'environment' => $this->config->getEnvironment(),
                    'kountMerchantId' => $this->config->getKountMerchantId(),
                    'hasFraudProtection' => $this->config->hasFraudProtection(),
                    'merchantId' => $this->config->getMerchantId(),
                    'ccVaultCode' => self::CC_VAULT_CODE
                ],
                Config::CODE_3DSECURE => [
                    'enabled' => $this->config->isVerify3DSecure(),
                    'thresholdAmount' => $this->config->getThresholdAmount(),
                    'specificCountries' => $this->config->get3DSecureSpecificCountries()
                ],
            ]
        ];
    }

    /**
     * Generate a new client token if necessary
     * @return string
     */
    public function getClientToken()
    {
        if (empty($this->clientToken)) {
            $params = [];

            $merchantAccountId = $this->config->getMerchantAccountId();
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->adapter->generate($params);
        }

        return $this->clientToken;
    }
}
