<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Plugin;

/**
 * Plugin for \Magento\Framework\Mail\TransportInterface
 * @since 2.1.0
 */
class WindowsSmtpConfig
{
    /**
     * host config path
     */
    const XML_SMTP_HOST = 'system/smtp/host';

    /**
     * port config path
     */
    const XML_SMTP_PORT = 'system/smtp/port';

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     * @since 2.1.0
     */
    private $config;

    /**
     * @var \Magento\Framework\OsInfo
     * @since 2.1.0
     */
    private $osInfo;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\OsInfo $osInfo
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\OsInfo $osInfo
    ) {
        $this->config = $config;
        $this->osInfo = $osInfo;
    }

    /**
     * To configure smtp settings for session right before sending message on windows server
     *
     * @param \Magento\Framework\Mail\TransportInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function beforeSendMessage(\Magento\Framework\Mail\TransportInterface $subject)
    {
        if ($this->osInfo->isWindows()) {
            ini_set('SMTP', $this->config->getValue(self::XML_SMTP_HOST));
            ini_set('smtp_port', $this->config->getValue(self::XML_SMTP_PORT));
        }
    }
}
