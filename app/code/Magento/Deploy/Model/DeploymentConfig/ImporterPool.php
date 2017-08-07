<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\ValidatorInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Pool of all deployment configuration importers.
 *
 * All importers should implement Magento\Framework\App\DeploymentConfig\ImporterInterface interface.
 * @since 2.2.0
 */
class ImporterPool
{
    /**
     * List of sections and their importers.
     *
     * Sections are defined with importers in di.xml. Every section may have data validator
     * E.g.
     * ```xml
     * <type name="Magento\Deploy\Model\DeploymentConfig\ImporterPool">
     *     <arguments>
     *          <argument name="importers" xsi:type="array">
     *               <item name="scopes" xsi:type="array">
     *                   <item name="sort_order" xsi:type="number">20</item>
     *                   <item name="importer_class" xsi:type="string">Magento\Store\Model\StoreImporter</item>
     *                   <item name="validator_class" xsi:type="string">Magento\Store\Model\Config\StoreValidator</item>
     *               </item>
     *               <item name="themes" xsi:type="array">
     *                   <item name="sort_order" xsi:type="number">10</item>
     *                   <item name="importer_class" xsi:type="string">Magento\Theme\Model\ThemeImporter</item>
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
     * @since 2.2.0
     */
    private $importers = [];

    /**
     * Sorted list of importers class names.
     *
     * This list sorted by parameter "sort_order", that defined in di.xml
     *
     * ```php
     * [
     *     'themes' => 'Magento\Theme\Model\ThemeImporter',
     *     'scopes' => 'Magento\Store\Model\StoreImporter',
     *     ...
     * ]
     * ```
     *
     * @var array
     * @since 2.2.0
     */
    private $sortedImporters = [];

    /**
     * Magento object manager.
     *
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * Factory that creates validator objects by class name.
     * Validators should be instances of Magento\Framework\App\DeploymentConfig\ValidatorInterface
     *
     * @var ValidatorFactory
     * @since 2.2.0
     */
    private $validatorFactory;

    /**
     * @param ObjectManagerInterface $objectManager the Magento object manager
     * @param ValidatorFactory $validatorFactory the validator factory
     * @param array $importers the list of sections and their importers
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ValidatorFactory $validatorFactory,
        array $importers = []
    ) {
        $this->objectManager = $objectManager;
        $this->validatorFactory = $validatorFactory;
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
     * @since 2.2.0
     */
    public function getSections()
    {
        return array_keys($this->importers);
    }

    /**
     * Retrieves list of all sections with their importer class names, sorted by sort_order.
     *
     * E.g.
     * ```php
     * [
     *     'scopes' => Magento\Store\Model\StoreImporter,
     *     ...
     * ]
     * ```
     *
     * @return array the list of all sections with their importer class names
     * @throws ConfigurationMismatchException is thrown when parameter class is empty
     * @since 2.2.0
     */
    public function getImporters()
    {
        if (!$this->sortedImporters) {
            $sortedImporters = [];

            foreach ($this->sort($this->importers) as $section => $importer) {
                if (empty($importer['importer_class'])) {
                    throw new ConfigurationMismatchException(__('Parameter "importer_class" must be present.'));
                }

                $sortedImporters[$section] = $importer['importer_class'];
            }

            $this->sortedImporters = $sortedImporters;
        }

        return $this->sortedImporters;
    }

    /**
     * Returns validator object for section if it was declared, otherwise returns null.
     *
     * @param string $section Section name
     * @return ValidatorInterface|null
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function getValidator($section)
    {
        if (isset($this->importers[$section]) && !empty($this->importers[$section]['validator_class'])) {
            return $this->validatorFactory->create($this->importers[$section]['validator_class']);
        }
        return null;
    }

    /**
     * Sorts importers according to sort order.
     *
     * @param array $data
     * @return array
     * @since 2.2.0
     */
    private function sort(array $data)
    {
        uasort($data, function (array $a, array $b) {
            $a['sort_order'] = $this->getSortOrder($a);
            $b['sort_order'] = $this->getSortOrder($b);

            if ($a['sort_order'] == $b['sort_order']) {
                return 0;
            }

            return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
        });

        return $data;
    }

    /**
     * Retrieves sort order from array.
     *
     * @param array $variable
     * @return int
     * @since 2.2.0
     */
    private function getSortOrder(array $variable)
    {
        return !empty($variable['sort_order']) ? $variable['sort_order'] : 0;
    }
}
