<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

/**
 * Configuration entry point for client using
 * @since 2.0.0
 */
class Config implements \Magento\Cron\Model\ConfigInterface
{
    /**
     * Cron config data
     *
     * @var \Magento\Cron\Model\Config\Data
     * @since 2.0.0
     */
    protected $_configData;

    /**
     * Initialize needed parameters
     *
     * @param \Magento\Cron\Model\Config\Data $configData
     * @since 2.0.0
     */
    public function __construct(\Magento\Cron\Model\Config\Data $configData)
    {
        $this->_configData = $configData;
    }

    /**
     * Return cron full cron jobs
     *
     * @return array
     * @since 2.0.0
     */
    public function getJobs()
    {
        return $this->_configData->getJobs();
    }
}
