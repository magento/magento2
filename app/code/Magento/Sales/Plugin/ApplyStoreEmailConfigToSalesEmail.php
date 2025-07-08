<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

class ApplyStoreEmailConfigToSalesEmail
{
    private const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Disable email sending depending on the system configuration setting
     *
     * @param IdentityInterface $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsEnabled(
        IdentityInterface $subject,
        $result
    ) {
        return $result && !$this->scopeConfig->isSetFlag(
            self::XML_PATH_SYSTEM_SMTP_DISABLE,
            ScopeInterface::SCOPE_STORE,
            $subject->getStore()->getStoreId()
        );
    }
}
