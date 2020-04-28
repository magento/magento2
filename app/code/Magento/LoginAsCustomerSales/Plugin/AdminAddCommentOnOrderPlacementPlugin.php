<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerSales\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Sales\Model\Order;

/**
 * Add comment after order placed by admin using admin panel.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AdminAddCommentOnOrderPlacementPlugin
{
    /**
     * @var Session
     */
    private $userSession;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->userSession = $session;
    }

    /**
     * Add comment after order placed by admin using admin panel.
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     */
    public function afterPlace(Order $subject, Order $result): Order
    {
        $adminUser = $this->userSession->getUser();
        if ($adminUser) {
            $subject->addCommentToStatusHistory(
                'Order Placed by Store Administrator',
                false,
                true
            )->setIsCustomerNotified(false);
            $subject->addCommentToStatusHistory(
                "Order Placed by {$adminUser->getFirstName()} {$adminUser->getLastName()} using Admin Panel",
                false,
                false
            )->setIsCustomerNotified(false);
        }

        return $result;
    }
}
