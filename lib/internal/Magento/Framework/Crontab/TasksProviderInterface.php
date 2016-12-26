<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

interface TasksProviderInterface
{
    /**
     * Get list of tasks
     *
     * @return array
     */
    public function getTasks();
}
