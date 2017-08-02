<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;

/**
 * Class ConfigProvider
 * @since 2.1.0
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'braintree';

    const CC_VAULT_CODE = 'braintree_cc_vault';

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @var BraintreeAdapter
     * @since 2.1.0
     */
    private $adapter;

    /**
     * @var string
     * @since 2.1.0
     */
    private $clientToken = '';

    /**
     * Constructor
     *
     * @param Config $config
     * @param BraintreeAdapter $adapter
     * @since 2.1.0
     */
    public function __construct(
        Config $config,
        BraintreeAdapter $adapter
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @since 2.1.0
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
     * @since 2.1.0
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
