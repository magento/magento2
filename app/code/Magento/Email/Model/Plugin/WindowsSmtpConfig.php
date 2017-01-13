<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Plugin;

/**
 * Plugin for \Magento\Framework\Mail\TransportInterface
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
     */
    private $config;

    /**
     * @var \Magento\Framework\OsInfo
     */
    private $osInfo;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\OsInfo $osInfo
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
     */
    public function beforeSendMessage(\Magento\Framework\Mail\TransportInterface $subject)
    {
        if ($this->osInfo->isWindows()) {
            ini_set('SMTP', $this->config->getValue(self::XML_SMTP_HOST));
            ini_set('smtp_port', $this->config->getValue(self::XML_SMTP_PORT));
        }
    }
}
