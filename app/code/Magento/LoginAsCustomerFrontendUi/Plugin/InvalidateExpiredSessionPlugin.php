<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomerFrontendUi\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerSessionActiveInterface;

/**
 * Invalidate expired and not active Login as Customer sessions.
 */
class InvalidateExpiredSessionPlugin
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var IsLoginAsCustomerSessionActiveInterface
     */
    private $isLoginAsCustomerSessionActive;

    /**
     * @param ConfigInterface $config
     * @param Session $session
     * @param IsLoginAsCustomerSessionActiveInterface $isLoginAsCustomerSessionActive
     */
    public function __construct(
        ConfigInterface $config,
        Session $session,
        IsLoginAsCustomerSessionActiveInterface $isLoginAsCustomerSessionActive
    ) {
        $this->session = $session;
        $this->isLoginAsCustomerSessionActive = $isLoginAsCustomerSessionActive;
        $this->config = $config;
    }

    /**
     * Invalidate expired and not active Login as Customer sessions.
     *
     * @param ActionInterface $subject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if ($this->config->isEnabled()) {
            $adminId = (int)$this->session->getLoggedAsCustomerAdmindId();
            $customerId = (int)$this->session->getCustomerId();
            if ($adminId && $customerId) {
                if (!$this->isLoginAsCustomerSessionActive->execute($customerId, $adminId)) {
                    $this->session->destroy();
                }
            }
        }
    }
}
