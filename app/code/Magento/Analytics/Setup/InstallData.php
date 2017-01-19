<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Analytics\Model\NotificationTime;
use Magento\Analytics\Model\IntegrationManager;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * @var IntegrationManager
     */
    private $integrationManager;

    /**
     * InstallData constructor.
     *
     * @param NotificationTime $notificationTime
     * @param IntegrationManager $integrationManager\
     */
    public function __construct(
        NotificationTime $notificationTime,
        IntegrationManager $integrationManager
    ) {
        $this->notificationTime = $notificationTime;
        $this->integrationManager = $integrationManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->notificationTime->storeLastTimeNotification(1);
        $this->integrationManager->createIntegration();
    }
}
