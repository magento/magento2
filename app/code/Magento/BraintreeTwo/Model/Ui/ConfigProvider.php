<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'braintreetwo';

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
     * @param BraintreeAdapter $adapter
     */
    public function __construct(Config $config, BraintreeAdapter $adapter)
    {
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
                ],
                Config::CODE_3DSECURE => [
                    'enabled' => $this->config->isVerify3DSecure(),
                    'thresholdAmount' => $this->config->getThresholdAmount(),
                    'specificCountries' => $this->config->get3DSecureSpecificCountries()
                ]
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
            $this->clientToken = $this->adapter->generate();
        }

        return $this->clientToken;
    }
}
