<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway;

use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Houses configuration for this gateway
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_LOGIN_ID = 'login';
    const KEY_TRANSACTION_KEY = 'trans_key';
    const KEY_API_URL = 'api_url';

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
    public function getLoginId($storeId = null)
    {
        return $this->getValue(Config::KEY_LOGIN_ID, $storeId);
    }

    /**
     * Gets the transaction key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTransactionKey($storeId = null)
    {
        return $this->getValue(Config::KEY_TRANSACTION_KEY, $storeId);
    }

    /**
     * Gets the API endpoint URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null)
    {
        return $this->getValue(Config::KEY_API_URL, $storeId);
    }
}
