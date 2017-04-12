<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Security\Model\SecurityManager;
use Magento\Customer\Model\AccountManagement as AccountManagementOriginal;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Security\Model\PasswordResetRequestEvent;

/**
 * Magento\Customer\Model\AccountManagement decorator
 */
class AccountManagement
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var int
     */
    protected $passwordRequestEvent;

    /**
     * AccountManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SecurityManager $securityManager
     * @param int $passwordRequestEvent
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Security\Model\SecurityManager $securityManager,
        $passwordRequestEvent = PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST
    ) {
        $this->request = $request;
        $this->securityManager = $securityManager;
        $this->passwordRequestEvent = $passwordRequestEvent;
    }

    /**
     * @param AccountManagementOriginal $accountManagement
     * @param string $email
     * @param string $template
     * @param int|null $websiteId
     * @return array
     * @throws SecurityViolationException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInitiatePasswordReset(
        AccountManagementOriginal $accountManagement,
        $email,
        $template,
        $websiteId = null
    ) {
        $this->securityManager->performSecurityCheck(
            $this->passwordRequestEvent,
            $email
        );
        return [$email, $template, $websiteId];
    }
}
