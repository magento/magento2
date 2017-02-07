<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
