<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomerFrontendUi\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
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
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @param ConfigInterface $config
     * @param Session $session
     * @param IsLoginAsCustomerSessionActiveInterface $isLoginAsCustomerSessionActive
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        ConfigInterface $config,
        Session $session,
        IsLoginAsCustomerSessionActiveInterface $isLoginAsCustomerSessionActive,
        GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
    ) {
        $this->session = $session;
        $this->isLoginAsCustomerSessionActive = $isLoginAsCustomerSessionActive;
        $this->config = $config;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId;
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
            $adminId = $this->getLoggedAsCustomerAdminId->execute();
            $customerId = (int)$this->session->getCustomerId();
            if ($adminId && $customerId) {
                if (!$this->isLoginAsCustomerSessionActive->execute($customerId, $adminId)) {
                    $this->session->clearStorage();
                    $this->session->expireSessionCookie();
                    $this->session->regenerateId();
                }
            }
        }
    }
}
