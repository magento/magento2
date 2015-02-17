<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModuleInstallerUpgraderFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        DirectoryList $directoryList
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->directoryList = $directoryList;
    }

    /**
     * Creates schema installer for a module
     *
     * @param $moduleName
     * @return InstallSchemaInterface | null
     */
    public function createSchemaInstaller($moduleName)
    {
        $modulePath = str_replace('_', '/', $moduleName);
        $schemaInstaller = $this->directoryList->getPath(DirectoryList::MODULES)
            . '/' . $modulePath . '/Setup/InstallSchema';
        return $this->getInstallerUpgrader($schemaInstaller);
    }

    /**
     * Creates schema upgrader for a module
     *
     * @param $moduleName
     * @return UpgradeSchemaInterface | null
     */
    public function createSchemaUpgrader($moduleName)
    {
        $modulePath = str_replace('_', '/', $moduleName);
        $schemaUpgrader = $this->directoryList->getPath(DirectoryList::MODULES)
            . '/' . $modulePath . '/Setup/UpgradeSchema';
        return $this->getInstallerUpgrader($schemaUpgrader);
    }

    /**
     * Get the installer or upgrader for a module
     *
     * @param $path
     * @return InstallSchemaInterface| UpgradeSchemaInterface| null
     */
    private function getInstallerUpgrader($path)
    {
        if (file_exists($path . '.php')) {
            $path = str_replace('/', '\\', str_replace(
                    $this->directoryList->getPath(DirectoryList::MODULES) . '/' ,
                    '',
                    $path
                ));
            return $this->serviceLocator->get($path);
        } else {
            return null;
        }
    }
}
