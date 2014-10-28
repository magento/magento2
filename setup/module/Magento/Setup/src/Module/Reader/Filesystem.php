<?php
/**
 * Module declaration reader. Reads module.xml declaration files from module /etc directories.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Module\Reader;

use Magento\Config\Reader\Filesystem as ConfigFilesystem;
use Magento\Setup\Module\FileResolver;
use Magento\Setup\Module\Converter\Dom;
use Magento\Setup\Module\SchemaLocator;
use Magento\Setup\Module\Dependency\ManagerInterface;

class Filesystem extends ConfigFilesystem
{
    /**
     * @var \Magento\Setup\Module\Dependency\ManagerInterface
     */
    protected $dependencyManager;

    /**
     * @var array
     */
    protected $idAttributes = array(
        '/config/module' => 'name',
        '/config/module/depends/extension' => 'name',
        '/config/module/depends/choice/extension' => 'name',
        '/config/module/sequence/module' => 'name'
    );

    /**
     * @param FileResolver $fileResolver
     * @param Dom $converter
     * @param SchemaLocator $schemaLocator
     * @param ManagerInterface $dependencyManager
     * @param string $fileName
     * @param string $domDocumentClass
     * @param array $idAttributes
     */
    public function __construct(
        FileResolver $fileResolver,
        Dom $converter,
        SchemaLocator $schemaLocator,
        ManagerInterface $dependencyManager,
        $fileName = 'module.xml',
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $idAttributes = array()
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $fileName,
            $domDocumentClass,
            $idAttributes
        );
        $this->dependencyManager = $dependencyManager;
    }

    /**
     * Load configuration
     *
     * @return array
     */
    public function read()
    {
        $activeModules = $this->filterActiveModules(parent::read());
        return $this->sortModules($activeModules);
    }

    /**
     * Retrieve declarations of active modules
     *
     * @param array $modules
     * @return array
     */
    protected function filterActiveModules(array $modules)
    {
        $activeModules = array();
        foreach ($modules as $moduleName => $moduleConfig) {
            if ($moduleConfig['active']) {
                $activeModules[$moduleName] = $moduleConfig;
            }
        }
        return $activeModules;
    }

    /**
     * Sort module declarations based on module dependencies
     *
     * @param array $modules
     * @return array
     */
    protected function sortModules(array $modules)
    {
        /**
         * The following map is needed only for sorting
         * (in order not to add extra information about dependencies to module config)
         */
        $moduleDependencyMap = array();
        foreach (array_keys($modules) as $moduleName) {
            $moduleDependencyMap[] = array(
                'moduleName' => $moduleName,
                'dependencies' => $this->dependencyManager->getExtendedModuleDependencies($moduleName, $modules)
            );
        }

        // Use "bubble sorting" because usort does not check each pair of elements and in this case it is important
        $modulesCount = count($moduleDependencyMap);
        for ($i = 0; $i < $modulesCount - 1; $i++) {
            for ($j = $i; $j < $modulesCount; $j++) {
                if (in_array($moduleDependencyMap[$j]['moduleName'], $moduleDependencyMap[$i]['dependencies'])) {
                    $temp = $moduleDependencyMap[$i];
                    $moduleDependencyMap[$i] = $moduleDependencyMap[$j];
                    $moduleDependencyMap[$j] = $temp;
                }
            }
        }

        $sortedModules = array();
        foreach ($moduleDependencyMap as $moduleDependencyPair) {
            $sortedModules[$moduleDependencyPair['moduleName']] = $modules[$moduleDependencyPair['moduleName']];
        }

        return $sortedModules;
    }
}
