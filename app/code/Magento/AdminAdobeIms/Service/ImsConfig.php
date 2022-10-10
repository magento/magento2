<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdobeIms\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class ImsConfig extends Config
{
    private const XML_PATH_LOGGING_ENABLED = 'adobe_ims/integration/logging_enabled';
    private const XML_PATH_NEW_ADMIN_EMAIL_TEMPLATE = 'adobe_ims/email/content_template';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        parent::__construct($scopeConfig, $url);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED
        );
    }

    /**
     * Check if module error-logging is enabled
     *
     * @return bool
     */
    public function loggingEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_LOGGING_ENABLED
        );
    }

    /**
     * Get email template for new created admin users
     *
     * @return string
     */
    public function getEmailTemplateForNewAdminUsers(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_NEW_ADMIN_EMAIL_TEMPLATE
        );
    }
}
