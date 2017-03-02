<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Phrase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;

/**
 * Pool of all deployment configuration importers.
 *
 * All importers should implement Magento\Framework\App\DeploymentConfig\ImporterInterface interface.
 */
class ImporterPool
{
    /**
     * List of sections and their importers.
     *
     * Sections are defined with importers in di.xml
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
     *
     * The example of section in deployment configuration file:
     * ```php
     * [
     *     'scopes' => [
     *         'websites' => [
     *              ...
     *         ],
     *         'groups' => [
     *              ...
     *         ],
     *         'stores' => [
     *              ...
     *         ],
     *       ...
     *     ]
     * ]
     * ```
     *
     * @var array
     */
    private $importers = [];

    /**
     * Magento object manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager the Magento object manager
     * @param array $importers the list of sections and their importers
     */
    public function __construct(ObjectManagerInterface $objectManager, array $importers = [])
    {
        $this->objectManager = $objectManager;
        $this->importers = $importers;
    }

    /**
     * Retrieves names of sections for configuration files whose data is read from these files for import
     * to appropriate application sources.
     *
     * @return array the list of sections
     * E.g.
     * ```php
     * [
     *     'scopes',
     *     'themes',
     *     ...
     * ]
     * ```
     */
    public function getSections()
    {
        return array_keys($this->importers);
    }

    /**
     * Retrieves list of all sections with their importer instances.
     *
     * E.g.
     * ```php
     * [
     *     'scopes' => SomeScopeImporter(),
     *     ...
     * ]
     * ```
     *
     * @return array the list of all sections with their importer instances
     * @throws ConfigurationMismatchException is thrown when instance of importer implements a wrong interface
     */
    public function getImporters()
    {
        $result = [];

        foreach ($this->importers as $section => $importer) {
            $importerObj = $this->objectManager->get($importer);
            if (!$importerObj instanceof ImporterInterface) {
                throw new ConfigurationMismatchException(new Phrase(
                    '%1: Instance of %2 is expected, got %3 instead',
                    [$section, ImporterInterface::class, get_class($importerObj)]
                ));
            }
            $result[$section] = $importerObj;
        }

        return $result;
    }
}
