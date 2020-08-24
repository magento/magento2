<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model\Customer;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\AuthorizationInterface;
use Magento\Integration\Api\AuthorizationServiceInterface as AuthorizationService;
use Magento\Persistent\Helper\Session as PersistentSession;

class Authorization implements AuthorizationInterface
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

    public function isAllowed(
        $resource,
        $privilege = null
    ) {
        if ($this->persistentSession->isPersistent()
            && $resource == AuthorizationService::PERMISSION_SELF
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && !$this->customerSession->isLoggedIn()
        ) {
            return false;
        }

        return true;
    }
}
