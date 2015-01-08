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
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->at(0))
            ->method('get')
            ->with(InitParamListener::BOOTSTRAP_PARAM)
            ->willReturn([]);
        $serviceLocatorMock
            ->expects($this->at(1))
            ->method('get')
            ->with('Magento\Setup\Model\FilePermissions')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Setup\Model\FilePermissions')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(2))
            ->method('get')
            ->with('Magento\Framework\App\DeploymentConfig\Writer')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\App\DeploymentConfig\Writer')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(3))
            ->method('get')
            ->with('Magento\Framework\App\DeploymentConfig')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(4))
            ->method('get')
            ->with('Magento\Setup\Module\SetupFactory')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Setup\Module\SetupFactory')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(5))
            ->method('get')
            ->with('Magento\Framework\Module\ModuleList')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\Module\ModuleList')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(6))
            ->method('get')
            ->with('Magento\Framework\Module\ModuleList\Loader')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\Module\ModuleList\Loader')
                    ->disableOriginalConstructor()
                    ->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(7))
            ->method('get')
            ->with('Magento\Framework\App\Filesystem\DirectoryList')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\App\Filesystem\DirectoryList')
                    ->disableOriginalConstructor()
                    ->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(8))
            ->method('get')
            ->with('Magento\Setup\Model\AdminAccountFactory')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Setup\Model\AdminAccountFactory')
                    ->disableOriginalConstructor()
                    ->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(9))
            ->method('get')
            ->with('Magento\Framework\Math\Random')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\Math\Random')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(10))
            ->method('get')
            ->with('Magento\Setup\Module\ConnectionFactory')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Setup\Module\ConnectionFactory')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(11))
            ->method('get')
            ->with('Magento\Framework\App\MaintenanceMode')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\App\MaintenanceMode')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(12))
            ->method('get')
            ->with('Magento\Framework\Filesystem')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Framework\Filesystem')->disableOriginalConstructor()->getMock()
            ));
        $serviceLocatorMock
            ->expects($this->at(13))
            ->method('get')
            ->with('Magento\Setup\Model\SampleData')
            ->will($this->returnValue(
                $this->getMockBuilder('Magento\Setup\Model\SampleData')->disableOriginalConstructor()->getMock()
            ));

        $log = $this->getMockForAbstractClass('Magento\Setup\Model\LoggerInterface');
        $installerFactory = new InstallerFactory($serviceLocatorMock);
        $installer = $installerFactory->create($log);
        $this->assertInstanceOf('Magento\Setup\Model\Installer', $installer);
    }
}
