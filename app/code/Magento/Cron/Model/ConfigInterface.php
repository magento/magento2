<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

/**
 * Interface \Magento\Cron\Model\ConfigInterface
 *
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Return list of cron jobs
     *
     * @return array
     * @since 2.0.0
     */
    public function getJobs();
}
