<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Contact module configuration
 * @since 2.2.0
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function emailTemplate()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function emailRecipient()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
