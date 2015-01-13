<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

use Magento\Setup\Mvc\Bootstrap\InitParamListener;

class InstallerFactoryTest Extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $returnValueMap = [
            [InitParamListener::BOOTSTRAP_PARAM, []],
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
        ];
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $log = $this->getMockForAbstractClass('Magento\Setup\Model\LoggerInterface');
        $installerFactory = new InstallerFactory($serviceLocatorMock);
        $installer = $installerFactory->create($log);
        $this->assertInstanceOf('Magento\Setup\Model\Installer', $installer);
    }
}
