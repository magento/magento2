<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Observer;

use Magento\AdminAdobeIms\Service\AdminNotificationService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;

class AdminAccountCreatedObserver implements ObserverInterface
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var AdminNotificationService
     */
    private AdminNotificationService $adminNotificationService;

    /**
     * @param ImsConfig $imsConfig
     * @param AdminNotificationService $adminNotificationService
     */
    public function __construct(
        ImsConfig $imsConfig,
        AdminNotificationService $adminNotificationService
    ) {
        $this->imsConfig = $imsConfig;
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->imsConfig->enabled()) {
            return;
        }

        /** @var User $user */
        $user = $observer->getObject();

        if ($user->isObjectNew()) {
            $this->adminNotificationService->sendWelcomeMailToAdminUser($user);
        }
    }
}
