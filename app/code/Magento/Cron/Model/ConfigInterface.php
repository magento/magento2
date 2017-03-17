<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

/**
 * Interface \Magento\Cron\Model\ConfigInterface
 */
interface ConfigInterface
{
    /**
     * Return list of cron jobs
     *
     * @return array
     */
    public function getJobs();
}
