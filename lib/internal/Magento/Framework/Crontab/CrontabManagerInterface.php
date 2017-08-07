<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface \Magento\Framework\Crontab\CrontabManagerInterface
 *
 * @since 2.2.0
 */
interface CrontabManagerInterface
{
    /**#@+
     * Constants for wrapping Magento section in crontab
     */
    const TASKS_BLOCK_START = '#~ MAGENTO START';
    const TASKS_BLOCK_END = '#~ MAGENTO END';
    /**#@-*/

    /**
     * Get list of Magento Tasks
     *
     * @return array
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function getTasks();

    /**
     * Save Magento Tasks to crontab
     *
     * @param array $tasks
     * @return void
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function saveTasks(array $tasks);

    /**
     * Remove Magento Tasks form crontab
     *
     * @return void
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function removeTasks();
}
