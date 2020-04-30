<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomerUi\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\LoginAsCustomer\Model\Config;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerSessionActiveInterface;
use Magento\Security\Model\AdminSessionInfoFactory;

/**
 * Invalidate expired and not active Login as Customer sessions.
 */
class InvalidateExpiredSessionPlugin
{
    /**
     * @var Config
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
     * @var AdminSessionInfoFactory
     */
    private $adminSessionInfoFactory;

    /**
     * @param Config $config
     * @param Session $session
     * @param AdminSessionInfoFactory $adminSessionInfoFactory
     * @param IsLoginAsCustomerSessionActiveInterface $isLoginAsCustomerSessionActive
     */
    public function __construct(
        Config $config,
        Session $session,
        AdminSessionInfoFactory $adminSessionInfoFactory,
        IsLoginAsCustomerSessionActiveInterface $isLoginAsCustomerSessionActive
    ) {
        $this->session = $session;
        $this->adminSessionInfoFactory = $adminSessionInfoFactory;
        $this->isLoginAsCustomerSessionActive = $isLoginAsCustomerSessionActive;
        $this->config = $config;
    }

    /**
     * Invalidate expired and not active Login as Customer sessions.
     *
     * @param ActionInterface $subject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
