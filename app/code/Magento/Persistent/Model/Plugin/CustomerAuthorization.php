<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model\Plugin;

use Closure;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Authorization;
use Magento\Integration\Api\AuthorizationServiceInterface as AuthorizationService;
use Magento\Persistent\Helper\Session as PersistentSession;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Performs the check if the customer is logged in prior placing order on his behalf when the persistent cart is active
 */
class CustomerAuthorization
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerSession $customerSession
     * @param PersistentSession $persistentSession
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerSession $customerSession,
        PersistentSession $persistentSession
    ) {
        $this->userContext = $userContext;
        $this->customerSession = $customerSession;
        $this->persistentSession = $persistentSession;
    }

    /**
     * Check if the customer is logged in prior placing order on his behalf when the persistent cart is active
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Authorization $subject
     * @param Closure $proceed
     * @param $resource
     * @param null $privilege
     * @return false|mixed
     */
    public function aroundIsAllowed(
        Authorization $subject,
        Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource == AuthorizationService::PERMISSION_SELF
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && !$this->customerSession->isLoggedIn()
            && $this->persistentSession->isPersistent()
        ) {
            return false;
        }

        return true;
    }
}
