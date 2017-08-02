<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

/**
 * Interface \Magento\Framework\Crontab\TasksProviderInterface
 *
 * @since 2.2.0
 */
interface TasksProviderInterface
{
    /**
     * Get list of tasks
     *
     * @return array
     * @since 2.2.0
     */
    public function getTasks();
}
