<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\AuthorizationInterface;
use Magento\Persistent\Helper\Session as PersistentSession;

/**
 * Authorization logic for persistent customers
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Authorization implements AuthorizationInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @param CustomerSession $customerSession
     * @param PersistentSession $persistentSession
     */
    public function __construct(
        CustomerSession $customerSession,
        PersistentSession $persistentSession
    ) {
        $this->customerSession = $customerSession;
        $this->persistentSession = $persistentSession;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAllowed(
        $resource,
        $privilege = null
    ) {
        if (
            $this->persistentSession->isPersistent() &&
            $this->customerSession->getCustomerId() &&
            $this->customerSession->getIsCustomerEmulated()
        ) {
            return false;
        }

        return true;
    }
}
