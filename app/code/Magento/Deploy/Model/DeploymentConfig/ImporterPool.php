<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;

/**
 * Pool of all deployment configuration importers.
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
     *               <item name="scopes" xsi:type="array">
     *                   <item name="sortOrder" xsi:type="number">20</item>
     *                   <item name="class" xsi:type="string">Magento\Store\Model\StoreImporter</item>
     *               </item>
     *               <item name="themes" xsi:type="array">
     *                   <item name="sortOrder" xsi:type="number">10</item>
     *                   <item name="class" xsi:type="string">Magento\Theme\Model\ThemeImporter</item>
     *               </item>
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
     * The same as $importers, sorted by sortOrder.
     *
     * @var array
     */
    private $sortedImporters;

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
     * Retrieves sections from deployment configuration files which need to import into the DB.
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
     * Retrieves list of all sections with their importer instances, sorted by sortOrder.
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

        if (null == $this->sortedImporters) {
            $this->sortedImporters = $this->sort($this->importers);
        }

        foreach ($this->sortedImporters as $section => $importer) {
            if (empty($importer['class'])) {
                throw new ConfigurationMismatchException(__('Parameter "class" must be present.'));
            }

            $importerObj = $this->objectManager->get($importer['class']);
            if (!$importerObj instanceof ImporterInterface) {
                throw new ConfigurationMismatchException(__(
                    '%1: Instance of %2 is expected, got %3 instead',
                    $section,
                    ImporterInterface::class, get_class($importerObj)
                ));
            }
            $result[$section] = $importerObj;
        }

        return $result;
    }

    /**
     * Sorts importers according to sort order.
     *
     * @param array $data
     * @return array
     */
    private function sort(array $data)
    {
        uasort($data, function (array $a, array $b) {
            $a['sortOrder'] = $this->getSortOrder($a);
            $b['sortOrder'] = $this->getSortOrder($b);

            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            }

            return ($a['sortOrder'] < $b['sortOrder']) ? -1 : 1;
        });

        return $data;
    }

    /**
     * Retrieves sort order from array.
     *
     * @param array $variable
     * @return int
     */
    private function getSortOrder(array $variable)
    {
        return !empty($variable['sortOrder']) ? $variable['sortOrder'] : 0;
    }
}
