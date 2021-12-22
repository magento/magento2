<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerSales\Plugin;

use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
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
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @param UserFactory $userFactory
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        UserFactory $userFactory,
        GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
    ) {
        $this->userFactory = $userFactory;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId;
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
        $adminId = $this->getLoggedAsCustomerAdminId->execute();
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
