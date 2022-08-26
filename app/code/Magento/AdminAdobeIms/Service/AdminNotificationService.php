<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Model\ImsEmailNotification;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;

class AdminNotificationService
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var BackendUrlInterface
     */
    private BackendUrlInterface $backendUrl;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ImsEmailNotification
     */
    private ImsEmailNotification $emailNotification;

    /**
     * @param ImsConfig $adminImsConfig
     * @param BackendUrlInterface $backendUrl
     * @param StoreManagerInterface $storeManager
     * @param ImsEmailNotification $emailNotification
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        BackendUrlInterface $backendUrl,
        StoreManagerInterface $storeManager,
        ImsEmailNotification $emailNotification
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->emailNotification = $emailNotification;
    }

    /**
     * Send a welcome mail to created admin user
     *
     * @param UserInterface $user
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendWelcomeMailToAdminUser(UserInterface $user): void
    {
        if (!$this->adminImsConfig->enabled()) {
            return;
        }

        $backendUrl = $this->backendUrl->getRouteUrl('adminhtml');

        $emailTemplate = $this->adminImsConfig->getEmailTemplateForNewAdminUsers();

        $this->emailNotification->sendNotificationEmail(
            $emailTemplate,
            [
                'user' => $user,
                'store' => $this->storeManager->getStore(
                    Store::DEFAULT_STORE_ID
                ),
                'cta_link' => $backendUrl
            ],
            $user->getEmail(),
            $user->getFirstName() . ' ' . $user->getLastName()
        );
    }
}
