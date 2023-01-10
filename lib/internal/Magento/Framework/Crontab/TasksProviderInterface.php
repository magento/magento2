<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Crontab;

/**
 * Interface \Magento\Framework\Crontab\TasksProviderInterface
 *
 * @api
 */
interface TasksProviderInterface
{
    /**
     * Get list of tasks
     *
     * @return array
     */
    public function getTasks();
}
