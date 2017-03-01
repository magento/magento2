<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;

/**
 * Config data collector of specific sections which are defined in di.xml
 */
class DataCollector
{
    /**
     * Pool of all deployment configuration importers.
     *
     * @var ImporterPool
     */
    private $configImporterPool;

    /**
     * Application deployment configuration.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ImporterPool $configImporterPool the pool of all deployment configuration importers
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     */
    public function __construct(ImporterPool $configImporterPool, DeploymentConfig $deploymentConfig)
    {
        $this->configImporterPool = $configImporterPool;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Retrieves configuration data of all specific sections from deployment configuration files.
     * Or retrieves configuration data of specific sections by its name.
     *
     * @param string $sectionName the section name for retrieving its configuration data
     * @return array is configurations data from deployment configuration files
     */
    public function getConfig($sectionName = null)
    {
        $result = [];

        if ($sectionName) {
            $sections = [$sectionName];
        } else {
            $sections = $this->configImporterPool->getSections();
        }

        foreach ($sections as $section) {
            $data = $this->deploymentConfig->getConfigData($section);
            if ($data) {
                $result[$section] = $data;
            }
        }

        return $result;
    }
}
