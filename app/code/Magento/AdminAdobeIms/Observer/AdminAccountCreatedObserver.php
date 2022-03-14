<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Observer;

use Magento\AdminAdobeIms\Service\AdminNotificationService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;

class AdminAccountCreatedObserver implements ObserverInterface
{
    /**
     * @var AdminNotificationService
     */
    private AdminNotificationService $adminNotificationService;

    /**
     * @param AdminNotificationService $adminNotificationService
     */
    public function __construct(
        AdminNotificationService $adminNotificationService
    ) {
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var User $user */
        $user = $observer->getObject();

        if ($user->isObjectNew()) {
            $this->adminNotificationService->sendWelcomeMailToAdminUser($user);
        }
    }
}
