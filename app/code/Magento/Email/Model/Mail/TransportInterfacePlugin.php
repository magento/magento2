<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Mail;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin over \Magento\Framework\Mail\TransportInterface
 *
 * It disables email sending depending on the system configuration settings
 */
class TransportInterfacePlugin
{
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
     * Omit email sending depending on the system configuration setting
     *
     * @param TransportInterface $subject
     * @param \Closure $proceed
     * @return void
     * @throws MailException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        \Closure $proceed
    ) {
        if (!$this->scopeConfig->isSetFlag('system/smtp/disable', ScopeInterface::SCOPE_STORE)) {
            $proceed();
        }
    }
}
