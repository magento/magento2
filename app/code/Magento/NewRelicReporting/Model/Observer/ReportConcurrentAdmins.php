<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportConcurrentAdmins
 * @since 2.0.0
 */
class ReportConcurrentAdmins implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\NewRelicReporting\Model\UsersFactory
     * @since 2.0.0
     */
    protected $usersFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $jsonEncoder;

    /**
     * @param Config $config
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\NewRelicReporting\Model\UsersFactory $usersFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\NewRelicReporting\Model\UsersFactory $usersFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->config = $config;
        $this->backendAuthSession = $backendAuthSession;
        $this->usersFactory = $usersFactory;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Reports concurrent admins to the database reporting_users table
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            if ($this->backendAuthSession->isLoggedIn()) {
                $user = $this->backendAuthSession->getUser();
                $jsonData = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getFirstname() . ' ' . $user->getLastname(),
                ];

                $modelData = [
                    'type' => 'admin_activity',
                    'action' => $this->jsonEncoder->encode($jsonData),
                ];

                /** @var \Magento\NewRelicReporting\Model\Users $usersModel */
                $usersModel = $this->usersFactory->create();
                $usersModel->setData($modelData);
                $usersModel->save();
            }
        }
    }
}
