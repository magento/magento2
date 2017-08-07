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
 * @since 2.1.0
 */
class AccountManagement
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.1.0
     */
    protected $request;

    /**
     * @var SecurityManager
     * @since 2.1.0
     */
    protected $securityManager;

    /**
     * @var int
     * @since 2.1.4
     */
    protected $passwordRequestEvent;

    /**
     * AccountManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SecurityManager $securityManager
     * @param int $passwordRequestEvent
     * @since 2.1.0
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
     * @since 2.1.0
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
