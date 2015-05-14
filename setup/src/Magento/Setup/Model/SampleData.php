<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;

/**
 * Sample data installer
 *
 * Serves as an integration point between Magento Setup application and Luma sample data component
 */
class SampleData
{
    /**
     * Path to the sample data application
     */
    const PATH = '/Magento/SampleData';

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Check if Sample Data was deployed
     *
     * @return bool
     */
    public function isDeployed()
    {
        return file_exists($this->directoryList->getPath(DirectoryList::MODULES) . self::PATH);
    }

    /**
     * Installation routine for creating sample data
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param string $userName
     * @param array $modules
     * @throws \Exception
     * @return void
     */
    public function install(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        $userName,
        array $modules = []
    ) {
        /** @var \Magento\SampleData\Model\Logger $sampleDataLogger */
        $sampleDataLogger = $objectManager->get('Magento\SampleData\Model\Logger');
        $sampleDataLogger->setSubject($logger);

        $areaCode = 'adminhtml';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\App\ObjectManager\ConfigLoader $configLoader */
        $configLoader = $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader');
        $objectManager->configure($configLoader->load($areaCode));

        /** @var \Magento\SampleData\Model\Installer $installer */
        $installer = $objectManager->get('Magento\SampleData\Model\Installer');
        $installer->run($userName, $modules);
    }
}
