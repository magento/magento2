<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerSampleData\Magento\Framework\Mail\Transport;

/**
 * Class AccountManagementPlugin
 */
class MailPlugin
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    public function __construct(\Magento\Framework\App\State $appState)
    {
        $this->appState = $appState;
    }

    /**
     * @param \Magento\Framework\Mail\TransportInterface $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundSendMessage(
        \Magento\Framework\Mail\TransportInterface $subject,
        \Closure $proceed
    ) {
        return $this->appState->isAreaCodeEmulated() ? null : $proceed();
    }
}
