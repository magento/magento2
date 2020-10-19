<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Setup;

use Magento\Cron\Model\ResourceModel\Schedule as ResourceModelSchedule;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Cron recurring setup
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var ResourceModelSchedule
     */
    private $schedule;

    /**
     * Recurring constructor.
     * @param ResourceModelSchedule $schedule
     */
    public function __construct(
        ResourceModelSchedule $schedule
    ) {
        $this->schedule = $schedule;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $this->schedule->getConnection();
        $connection->update(
            $this->schedule->getMainTable(),
            [
                'status' => Schedule::STATUS_ERROR,
                'messages' => 'The job is terminated due to system upgrade'
            ],
            $connection->quoteInto('status = ?', Schedule::STATUS_RUNNING)
        );
    }
}
