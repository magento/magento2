<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;

/**
 * Config data collector of specific sections in configuration files which are defined in di.xml
 *
 * E.g., definition of sections which are needed to import:
 * ```xml
 * <type name="Magento\Deploy\Model\DeploymentConfig\ImporterPool">
 *     <arguments>
 *          <argument name="importers" xsi:type="array">
 *               <item name="scopes" xsi:type="string">Magento\SomeModule\Model\SomeImporter</item>
 *          </argument>
 *     </arguments>
 * </type>
 * ```
 * Example, how sections are stored with their config data in configuration files:
 * ```php
 *  [
 *      'scopes' => [...],
 *      'system' => [...],
 *      'themes' => [...],
 *      ...
 *  ]
 * ```
 *
 * In here we define section "scopes" and its importer Magento\SomeModule\Model\SomeImporter.
 * The data of this section will be collected then will be used in importing process from the shared configuration
 * files to appropriate application sources.
 *
 * @see \Magento\Deploy\Console\Command\App\ConfigImport\Processor::execute()
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
     * Or retrieves configuration data of specific sections by its name.
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
     *
     * This method retrieves the same structure for the specific section with only its data.
     * ```php
     *  [
     *      'scopes' => [...]
     *  ]
     *
     * In this example key of the array is the section name, value of the array is configuration data of the section.
     *
     * @param string $sectionName the section name for retrieving its configuration data
     * @return array
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
            $result[$section] = $this->deploymentConfig->getConfigData($section);
        }

        return $result;
    }
}
