<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;

/**
 * Config data collector of specific sections which are defined in di.xml
 *
 * E.g.
 * ```xml
 * <type name="Magento\Deploy\Model\DeploymentConfig\ImporterPool">
 *     <arguments>
 *          <argument name="importers" xsi:type="array">
 *               <item name="scopes" xsi:type="string">Magento\Store\Model\StoreImporter</item>
 *          </argument>
 *     </arguments>
 * </type>
 * ```
 * In here we define section "scopes" and its importer Magento\Store\Model\StoreImporter.
 * The data of this section will be collected then will be used in importing process from the shared configuration
 * files to appropriate application sources.
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
     * Retrieves configuration data of specific section from deployment configuration files.
     *
     * E.g.
     * ```php
     *  [
     *      'scopes' => [...],
     *      'system' => [...],
     *      'themes' => [...],
     *      ...
     *  ]
     * ```
     * In this example key of the array is the section name, value of the array is configuration data of the section.
     *
     * @return array
     */
    public function getConfig()
    {
        $result = [];

        foreach ($this->configImporterPool->getSections() as $section) {
            $data = $this->deploymentConfig->getConfigData($section);
            if ($data) {
                $result[$section] = $data;
            }
        }

        return $result;
    }
}
