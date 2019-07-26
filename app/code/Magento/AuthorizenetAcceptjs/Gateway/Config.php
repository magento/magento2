<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway;

/**
 * Houses configuration for this gateway
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * @var string
     */
    private static $keyLoginId = 'login';

    /**
     * @var string
     */
    private static $keyTransactionKey = 'trans_key';

    /**
     * @var string
     */
    private static $keyEnvironment = 'environment';

    /**
     * @var string
     */
    private static $keyLegacyTransactionHash = 'trans_md5';

    /**
     * @var string
     */
    private static $keySignatureKey = 'trans_signature_key';

    /**
     * @var string
     */
    private static $keyPaymentAction = 'payment_action';

    /**
     * @var string
     */
    private static $keyShouldEmailCustomer = 'email_customer';

    /**
     * @var string
     */
    private static $keyAdditionalInfoKeys = 'paymentInfoKeys';

    /**
     * @var string
     */
    private static $keyClientKey = 'public_client_key';

    /**
     * @var string
     */
    private static $keyCvvEnabled = 'cvv_enabled';

    /**
     * @var string
     */
    private static $keyTransactionSyncKeys = 'transactionSyncKeys';

    /**
     * @var string
     */
    private static $endpointUrlSandbox = 'https://apitest.authorize.net/xml/v1/request.api';

    /**
     * @var string
     */
    private static $endpointUrlProduction = 'https://api.authorize.net/xml/v1/request.api';

    /**
     * @var string
     */
    private static $solutionIdSandbox = 'AAA102993';

    /**
     * @var string
     */
    private static $solutionIdProduction = 'AAA175350';

    /**
     * Gets the login id
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLoginId($storeId = null)
    {
        return $this->getValue(self::$keyLoginId, $storeId);
    }

    /**
     * Gets the current environment
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null): string
    {
        return $this->getValue(self::$keyEnvironment, $storeId);
    }

    /**
     * Gets the transaction key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTransactionKey($storeId = null)
    {
        return $this->getValue(self::$keyTransactionKey, $storeId);
    }

    /**
     * Gets the API endpoint URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null): string
    {
        $environment = $this->getValue(self::$keyEnvironment, $storeId);

        return $environment === 'sandbox'
            ? self::$endpointUrlSandbox
            : self::$endpointUrlProduction;
    }

    /**
     * Gets the configured signature key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTransactionSignatureKey($storeId = null)
    {
        return $this->getValue(self::$keySignatureKey, $storeId);
    }

    /**
     * Gets the configured legacy transaction hash
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLegacyTransactionHash($storeId = null)
    {
        return $this->getValue(self::$keyLegacyTransactionHash, $storeId);
    }

    /**
     * Gets the configured payment action
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentAction($storeId = null)
    {
        return $this->getValue(self::$keyPaymentAction, $storeId);
    }

    /**
     * Gets the configured client key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getClientKey($storeId = null)
    {
        return $this->getValue(self::$keyClientKey, $storeId);
    }

    /**
     * Should authorize.net email the customer their receipt.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function shouldEmailCustomer($storeId = null): bool
    {
        return (bool)$this->getValue(self::$keyShouldEmailCustomer, $storeId);
    }

    /**
     * Should the cvv field be shown
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCvvEnabled($storeId = null): bool
    {
        return (bool)$this->getValue(self::$keyCvvEnabled, $storeId);
    }

    /**
     * Retrieves the solution id for the given store based on environment
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSolutionId($storeId = null)
    {
        $environment = $this->getValue(self::$keyEnvironment, $storeId);

        return $environment === 'sandbox'
            ? self::$solutionIdSandbox
            : self::$solutionIdProduction;
    }

    /**
     * Returns the keys to be pulled from the transaction and displayed
     *
     * @param int|null $storeId
     * @return string[]
     */
    public function getAdditionalInfoKeys($storeId = null): array
    {
        return explode(',', $this->getValue(self::$keyAdditionalInfoKeys, $storeId) ?? '');
    }

    /**
     * Returns the keys to be pulled from the transaction and displayed when syncing the transaction
     *
     * @param int|null $storeId
     * @return string[]
     */
    public function getTransactionInfoSyncKeys($storeId = null): array
    {
        return explode(',', $this->getValue(self::$keyTransactionSyncKeys, $storeId) ?? '');
    }
}
