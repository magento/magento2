<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

use Magento\Customer\Model\AccountManagement as AccountManagementOriginal;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\SecurityManager;

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
     * @var ScopeInterface
     */
    private $scope;

    /**
     * AccountManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SecurityManager $securityManager
     * @param int $passwordRequestEvent
     * @param ScopeInterface|null $scope
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Security\Model\SecurityManager $securityManager,
        $passwordRequestEvent = PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
        ?ScopeInterface $scope = null
    ) {
        $this->request = $request;
        $this->securityManager = $securityManager;
        $this->passwordRequestEvent = $passwordRequestEvent;
        $this->scope = $scope ?: ObjectManager::getInstance()->get(ScopeInterface::class);
    }

    /**
     * Security check before reset password
     *
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
        if ($this->scope->getCurrentScope() == Area::AREA_FRONTEND
            || $this->passwordRequestEvent == PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST
            || ($this->scope->getCurrentScope() == Area::AREA_WEBAPI_REST
            && $this->passwordRequestEvent == PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST)) {
            $this->securityManager->performSecurityCheck(
                $this->passwordRequestEvent,
                $email
            );
        }

        return [$email, $template, $websiteId];
    }
}
