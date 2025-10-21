<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cron\Model;

/**
 * Interface \Magento\Cron\Model\ConfigInterface
 *
 * @api
 * @since 100.0.2
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
