<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Session\SessionManagerInterface;

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
     * @var BraintreeAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @param Config $config
     * @param PayPalConfig $payPalConfig No longer used by internal code and not recommended.
     * @param BraintreeAdapter $adapter No longer used by internal code and not recommended.
     * @param ResolverInterface $localeResolver No longer used by internal code and not recommended.
     * @param SessionManagerInterface|null $session
     * @param BraintreeAdapterFactory|null $adapterFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $config,
        PayPalConfig $payPalConfig,
        BraintreeAdapter $adapter,
        ResolverInterface $localeResolver,
        SessionManagerInterface $session = null,
        BraintreeAdapterFactory $adapterFactory = null
    ) {
        $this->config = $config;
        $this->adapterFactory = $adapterFactory ?: ObjectManager::getInstance()->get(BraintreeAdapterFactory::class);
        $this->session = $session ?: ObjectManager::getInstance()->get(SessionManagerInterface::class);
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isActive($storeId),
                    'clientToken' => $this->getClientToken(),
                    'ccTypesMapper' => $this->config->getCcTypesMapper(),
                    'sdkUrl' => $this->config->getSdkUrl(),
                    'countrySpecificCardTypes' => $this->config->getCountrySpecificCardTypeConfig($storeId),
                    'availableCardTypes' => $this->config->getAvailableCardTypes($storeId),
                    'useCvv' => $this->config->isCvvEnabled($storeId),
                    'environment' => $this->config->getEnvironment($storeId),
                    'kountMerchantId' => $this->config->getKountMerchantId($storeId),
                    'hasFraudProtection' => $this->config->hasFraudProtection($storeId),
                    'merchantId' => $this->config->getMerchantId($storeId),
                    'ccVaultCode' => self::CC_VAULT_CODE
                ],
                Config::CODE_3DSECURE => [
                    'enabled' => $this->config->isVerify3DSecure($storeId),
                    'thresholdAmount' => $this->config->getThresholdAmount($storeId),
                    'specificCountries' => $this->config->get3DSecureSpecificCountries($storeId)
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

            $storeId = $this->session->getStoreId();
            $merchantAccountId = $this->config->getMerchantAccountId($storeId);
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->adapterFactory->create($storeId)
                ->generate($params);
        }

        return $this->clientToken;
    }
}
