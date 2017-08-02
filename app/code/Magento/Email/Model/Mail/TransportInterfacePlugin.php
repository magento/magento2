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
 * Class \Magento\Email\Model\Mail\TransportInterfacePlugin
 *
 * @since 2.2.0
 */
class TransportInterfacePlugin
{
    /**
     * Config path to mail sending setting that shows if email communications are disabled
     */
    const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Omit email sending if disabled
     *
     * @param TransportInterface $subject
     * @param \Closure $proceed
     * @return void
     * @throws MailException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        \Closure $proceed
    ) {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_SYSTEM_SMTP_DISABLE, ScopeInterface::SCOPE_STORE)) {
            $proceed();
        }
    }
}
