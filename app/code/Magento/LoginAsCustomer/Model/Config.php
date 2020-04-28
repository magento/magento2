<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

/**
 * @inheritdoc
 */
class Config implements ConfigInterface
{
    /**
     * Extension config path
     */
    private const XML_PATH_ENABLED
        = 'login_as_customer/general/enabled';
    private const XML_PATH_STORE_VIEW_MANUAL_CHOICE_ENABLED
        = 'login_as_customer/general/store_view_manual_choice_enabled';
    private const XML_PATH_AUTHENTICATION_EXPIRATION_TIME
        = 'login_as_customer/general/authentication_data_expiration_time';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function isStoreManualChoiceEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_STORE_VIEW_MANUAL_CHOICE_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function getAuthenticationDataExpirationTime(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_AUTHENTICATION_EXPIRATION_TIME);
    }
}
