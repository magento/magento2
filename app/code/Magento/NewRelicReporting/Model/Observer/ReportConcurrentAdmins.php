<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportConcurrentAdmins
 */
class ReportConcurrentAdmins
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\NewRelicReporting\Model\UsersFactory
     */
    protected $usersFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\NewRelicReporting\Model\UsersFactory $usersFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\NewRelicReporting\Model\UsersFactory $usersFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->config = $config;
        $this->backendAuthSession = $backendAuthSession;
        $this->usersFactory = $usersFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->dateTime = $dateTime;
    }

    /**
     * Reports concurrent admins to the database reporting_users table
     *
     * @return \Magento\NewRelicReporting\Model\Observer\ReportConcurrentAdmins
     */
    public function execute()
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
                    'updated_at' => $this->dateTime->formatDate(true),
                ];

                /** @var \Magento\NewRelicReporting\Model\Users $usersModel */
                $usersModel = $this->usersFactory->create();
                $usersModel->setData($modelData);
                $usersModel->save();
            }
        }

        return $this;
    }
}
