<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Analytics\Model\NotificationTime;

/**
 * @codeCoverageIgnore
 * @since 2.2.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var NotificationTime
     * @since 2.2.0
     */
    private $notificationTime;

    /**
     * InstallData constructor.
     *
     * @param NotificationTime $notificationTime
     * @since 2.2.0
     */
    public function __construct(
        NotificationTime $notificationTime
    ) {
        $this->notificationTime = $notificationTime;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.2.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->notificationTime->storeLastTimeNotification(1);
    }
}
