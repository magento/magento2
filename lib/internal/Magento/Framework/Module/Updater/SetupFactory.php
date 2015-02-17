<?php
/**
 * Module setup factory. Creates setups used during application install/upgrade.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Updater;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class SetupFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DirectoryList $directoryList
     */
    public function __construct(ObjectManagerInterface $objectManager, DirectoryList $directoryList)
    {
        $this->_objectManager = $objectManager;
        $this->directoryList = $directoryList;
    }

    /**
     * @param string $moduleName
     * @param string $type
     * @return InstallDataInterface |UpgradeDataInterface | null
     */
    public function create($moduleName, $type)
    {
        $modulePath = str_replace('_', '\\', $moduleName);
        if ($type === 'install') {
            $dataInstaller = $this->directoryList->getPath(DirectoryList::MODULES)
                . '/' . $modulePath . '/Setup/InstallData';
            return $this->getInstallerUpgrader($dataInstaller);
        } else {
            $dataUgrader = $this->directoryList->getPath(DirectoryList::MODULES)
                . '/' . $modulePath . '/Setup/UpgradeData';
            return $this->getInstallerUpgrader($dataUgrader);
        }
    }

    /**
     * Get the installer or upgrader for a module
     *
     * @param $path
     * @return InstallDataInterface |UpgradeDataInterface | null
     */
    private function getInstallerUpgrader($path)
    {
        if (file_exists($path . '.php')) {
            $path = str_replace('/', '\\', str_replace(
                $this->directoryList->getPath(DirectoryList::MODULES) . '/' ,
                '',
                $path
            ));
            return $this->_objectManager->create($path);
        } else {
            return null;
        }
    }
}
