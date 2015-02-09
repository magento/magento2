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
    const PATH = 'dev/tools/Magento/Tools/SampleData';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $rootDir;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * Check if Sample Data was deployed
     *
     * @return bool
     */
    public function isDeployed()
    {
        return $this->rootDir->isExist(self::PATH);
    }

    /**
     * Installation routine for creating sample data
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param $adminUserName
     * @throws \Exception
     */
    public function install(ObjectManagerInterface $objectManager, LoggerInterface $logger, $adminUserName)
    {
        /** @var \Magento\Tools\SampleData\Logger $sampleDataLogger */
        $sampleDataLogger = $objectManager->get('Magento\Tools\SampleData\Logger');
        $sampleDataLogger->setSubject($logger);

        $areaCode = 'adminhtml';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\App\ObjectManager\ConfigLoader $configLoader */
        $configLoader = $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader');
        $objectManager->configure($configLoader->load($areaCode));

        /** @var \Magento\User\Model\UserFactory $userFactory */
        $userFactory = $objectManager->get('Magento\User\Model\UserFactory');
        $user = $userFactory->create()->loadByUsername($adminUserName);

        $installer = $objectManager->get('Magento\Tools\SampleData\Installer');
        $installer->run($user);
    }
}
