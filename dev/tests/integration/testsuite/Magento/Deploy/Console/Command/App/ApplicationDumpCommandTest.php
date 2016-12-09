<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationDumpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationDumpCommand
     */
    private $command;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->command = Bootstrap::getObjectManager()->get(ApplicationDumpCommand::class);
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testExecute()
    {
        $inputMock = $this->getMock(InputInterface::class);
        $outputMock = $this->getMock(OutputInterface::class);
        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('<info>Done.</info>');
        $this->assertEquals(0, $this->command->run($inputMock, $outputMock));
    }

    public function tearDown()
    {
        /** @var ConfigFilePool $configFilePool */
        $configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $filePool = $configFilePool->getInitialFilePools();
        $file = $filePool[ConfigFilePool::LOCAL][ConfigFilePool::APP_CONFIG];
        /** @var DirectoryList $dirList */
        $dirList = $this->objectManager->get(DirectoryList::class);
        $path = $dirList->getPath(DirectoryList::CONFIG);
        $driverPool = $this->objectManager->get(DriverPool::class);
        $fileDriver = $driverPool->getDriver(DriverPool::FILE);
        if ($fileDriver->isExists($path . '/' . $file)) {
            unlink($path . '/' . $file);
        }
        /** @var DeploymentConfig $deploymentConfig */
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $deploymentConfig->resetData();
    }
}
