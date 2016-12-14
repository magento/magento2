<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

interface CrontabManagerInterface
{
    const TASKS_BLOCK_START = '#~ MAGENTO START';
    const TASKS_BLOCK_END = '#~ MAGENTO END';

    /**
     * Get list of Magento Tasks
     *
     * @return array
     */
    public function getTasks();

    /**
     * Save Magento Tasks to crontab
     *
     * @param array $tasks
     * @return void
     */
    public function saveTasks(array $tasks);

    /**
     * Remove Magento Tasks form crontab
     *
     * @return void
     */
    public function removeTasks();
}
