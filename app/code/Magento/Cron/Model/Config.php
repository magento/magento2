<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

/**
 * Configuration entry point for client using
 */
class Config implements \Magento\Cron\Model\ConfigInterface
{
    /**
     * Cron config data
     *
     * @var \Magento\Cron\Model\Config\Data
     */
    protected $_configData;

    /**
     * Initialize needed parameters
     *
     * @param \Magento\Cron\Model\Config\Data $configData
     */
    public function __construct(\Magento\Cron\Model\Config\Data $configData)
    {
        $this->_configData = $configData;
    }

    /**
     * Return cron full cron jobs
     *
     * @return array
     */
    public function getJobs()
    {
        return $this->_configData->getJobs();
    }
}
