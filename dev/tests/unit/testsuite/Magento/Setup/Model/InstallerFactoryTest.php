<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

class InstallerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $returnValueMap = [
            [
                'Magento\Setup\Model\FilePermissions',
                $this->getMock('Magento\Setup\Model\FilePermissions', [], [], '', false),
            ],
            [
                'Magento\Framework\App\DeploymentConfig\Writer',
                $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false),
            ],
            [
                'Magento\Framework\App\DeploymentConfig',
                $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false),
            ],
            [
                'Magento\Setup\Module\SetupFactory',
                $this->getMock('Magento\Setup\Module\SetupFactory', [], [], '', false),
            ],
            [
                'Magento\Framework\Module\ModuleList',
                $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false),
            ],
            [
                'Magento\Framework\Module\ModuleList\Loader',
                $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false),
            ],
            [
                'Magento\Framework\App\Filesystem\DirectoryList',
                $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false),
            ],
            [
                'Magento\Setup\Model\AdminAccountFactory',
                $this->getMock('Magento\Setup\Model\AdminAccountFactory', [], [], '', false),
            ],
            [
                'Magento\Framework\Math\Random',
                $this->getMock('Magento\Framework\Math\Random', [], [], '', false),
            ],
            [
                'Magento\Setup\Module\ConnectionFactory',
                $this->getMock('Magento\Setup\Module\ConnectionFactory', [], [], '', false),
            ],
            [
                'Magento\Framework\App\MaintenanceMode',
                $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false),
            ],
            [
                'Magento\Framework\Filesystem',
                $this->getMock('Magento\Framework\Filesystem', [], [], '', false),
            ],
            [
                'Magento\Setup\Model\SampleData',
                $this->getMock('Magento\Setup\Model\SampleData', [], [], '', false),
            ],
            [
                'Magento\Setup\Model\ObjectManagerFactory',
                $this->getMock('Magento\Setup\Model\ObjectManagerFactory', [], [], '', false),
            ],
            [
                'Magento\Framework\App\DeploymentConfig\Reader',
                $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false),
            ],
            [
                'Magento\Setup\Module\ModuleInstallerUpgraderFactory',
                $this->getMock('Magento\Setup\Module\ModuleInstallerUpgraderFactory', [], [], '', false),
            ],
        ];
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $log = $this->getMockForAbstractClass('Magento\Setup\Model\LoggerInterface');
        $resourceFactoryMock = $this->getMock('Magento\Setup\Module\ResourceFactory', [], [], '', false);
        $resourceFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMock('Magento\Framework\App\Resource', [], [], '', false)));
        $installerFactory = new InstallerFactory($serviceLocatorMock, $resourceFactoryMock);
        $installer = $installerFactory->create($log);
        $this->assertInstanceOf('Magento\Setup\Model\Installer', $installer);
    }
}
