<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerSales\Plugin;

use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;
use Magento\User\Model\UserFactory;

/**
 * Add comment after order placed by admin using Login as Customer.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class FrontAddCommentOnOrderPlacementPlugin
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @param Session $session
     * @param UserFactory $userFactory
     */
    public function __construct(
        Session $session,
        UserFactory $userFactory
    ) {
        $this->customerSession = $session;
        $this->userFactory = $userFactory;
    }

    /**
     * Add comment after order placed by admin using Login as Customer.
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     */
    public function afterPlace(Order $subject, Order $result): Order
    {
        $adminId = $this->customerSession->getLoggedAsCustomerAdmindId();
        if ($adminId) {
            $adminUser = $this->userFactory->create()->load($adminId);
            $subject->addCommentToStatusHistory(
                'Order Placed by Store Administrator',
                false,
                true
            )->setIsCustomerNotified(false);
            $subject->addCommentToStatusHistory(
                "Order Placed by {$adminUser->getFirstName()} {$adminUser->getLastName()} using Login as Customer",
                false,
                false
            )->setIsCustomerNotified(false);
        }

        return $result;
    }
}
