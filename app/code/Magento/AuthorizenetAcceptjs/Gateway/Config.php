<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway;

use Magento\AuthorizenetAcceptjs\Model\Adminhtml\Source\Environment;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Houses configuration for this gateway
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const METHOD = 'authorizenet_acceptjs';
    private const KEY_LOGIN_ID = 'login';
    private const KEY_TRANSACTION_KEY = 'trans_key';
    private const KEY_ENVIRONMENT = 'environment';
    private const KEY_LEGACY_TRANSACTION_HASH = 'trans_md5';
    private const KEY_SIGNATURE_KEY = 'trans_signature_key';
    private const KEY_PAYMENT_ACTION = 'payment_action';
    private const KEY_SHOULD_EMAIL_CUSTOMER = 'email_customer';
    private const KEY_ADDITIONAL_INFO_KEYS = 'paymentInfoKeys';
    private const KEY_CLIENT_KEY = 'public_client_key';
    private const KEY_CVV_ENABLED = 'cvv_enabled';
    private const KEY_TRANSACTION_SYNC_KEYS = 'transactionSyncKeys';
    private const ENDPOINT_URL_SANDBOX = 'https://apitest.authorize.net/xml/v1/request.api';
    private const ENDPOINT_URL_PRODUCTION = 'https://api.authorize.net/xml/v1/request.api';
    private const SOLUTION_ID_SANDBOX = 'AAA102993';
    private const SOLUTION_ID_PRODUCTION = 'AAA175350';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param null|string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Gets the login id
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLoginId($storeId = null): ?string
    {
        return $this->getValue(Config::KEY_LOGIN_ID, $storeId);
    }

    /**
     * Gets the current environment
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null): string
    {
        return $this->getValue(Config::KEY_ENVIRONMENT, $storeId);
    }

    /**
     * Gets the transaction key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTransactionKey($storeId = null): ?string
    {
        return $this->getValue(Config::KEY_TRANSACTION_KEY, $storeId);
    }

    /**
     * Gets the API endpoint URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null): string
    {
        $environment = $this->getValue(Config::KEY_ENVIRONMENT, $storeId);

        return $environment === Environment::ENVIRONMENT_SANDBOX
            ? self::ENDPOINT_URL_SANDBOX
            : self::ENDPOINT_URL_PRODUCTION;
    }

    /**
     * Gets the configured signature key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTransactionSignatureKey($storeId = null): ?string
    {
        return $this->getValue(Config::KEY_SIGNATURE_KEY, $storeId);
    }

    /**
     * Gets the configured legacy transaction hash
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLegacyTransactionHash($storeId = null): ?string
    {
        return $this->getValue(Config::KEY_LEGACY_TRANSACTION_HASH, $storeId);
    }

    /**
     * Gets the configured payment action
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentAction($storeId = null): ?string
    {
        return $this->getValue(Config::KEY_PAYMENT_ACTION, $storeId);
    }

    /**
     * Gets the configured client key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getClientKey($storeId = null): ?string
    {
        return $this->getValue(Config::KEY_CLIENT_KEY, $storeId);
    }

    /**
     * Should authorize.net email the customer their receipt.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function shouldEmailCustomer($storeId = null): bool
    {
        return (bool)$this->getValue(Config::KEY_SHOULD_EMAIL_CUSTOMER, $storeId);
    }

    /**
     * Should the cvv field be shown
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCvvEnabled($storeId = null): bool
    {
        return (bool)$this->getValue(Config::KEY_CVV_ENABLED, $storeId);
    }

    /**
     * Retrieves the solution id for the given store based on environment
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSolutionId($storeId = null): ?string
    {
        $environment = $this->getValue(Config::KEY_ENVIRONMENT, $storeId);

        return $environment === Environment::ENVIRONMENT_SANDBOX
            ? self::SOLUTION_ID_SANDBOX
            : self::SOLUTION_ID_PRODUCTION;
    }

    /**
     * Returns the keys to be pulled from the transaction and displayed
     *
     * @param int|null $storeId
     * @return string[]
     */
    public function getAdditionalInfoKeys($storeId = null): array
    {
        return explode(',', $this->getValue(Config::KEY_ADDITIONAL_INFO_KEYS, $storeId) ?? '');
    }

    /**
     * Returns the keys to be pulled from the transaction and displayed when syncing the transaction
     *
     * @param int|null $storeId
     * @return string[]
     */
    public function getTransactionInfoSyncKeys($storeId = null): array
    {
        return explode(',', $this->getValue(Config::KEY_TRANSACTION_SYNC_KEYS, $storeId) ?? '');
    }
}
