<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * AccountManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SecurityManager $securityManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Security\Model\SecurityManager $securityManager
    ) {
        $this->request = $request;
        $this->securityManager = $securityManager;
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
            PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
            $email
        );
        return [$email, $template, $websiteId];
    }
}
