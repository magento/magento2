<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\Flag\FlagResource;

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
     * @var FlagResource
     */
    private $flagResource;

    public function __construct(
        NotificationTime $notificationTime,
        FlagResource $flagResource
    ) {
        $this->notificationTime = $notificationTime;
         $this->flagResource = $flagResource;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->notificationTime->storeLastTimeNotification(1);
    }
}
