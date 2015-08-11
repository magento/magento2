<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\ComponentUninstallerFactory;
use Magento\Setup\Model\Cron\JobComponentUninstall;

class ComponentUninstallerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceManager
     */
    private $serviceLocator;

    /**
     * @var ComponentUninstallerFactory
     */
    private $factory;

    public function setUp()
    {
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->serviceLocator = $this->getMock('Zend\ServiceManager\ServiceManager', [], [], '', false);
        $this->factory = new ComponentUninstallerFactory($this->objectManagerProvider, $this->serviceLocator);
    }

    public function testCreateModule()
    {
        $this->objectManagerProvider->expects($this->never())->method($this->anything());
        $moduleUninstaller = $this->getMock('Magento\Setup\Model\ModuleUninstaller', [], [], '', false);
        $this->serviceLocator->expects($this->once())
            ->method('get')
            ->with('Magento\Setup\Model\ModuleUninstaller')
            ->willReturn($moduleUninstaller);
        $this->assertSame($moduleUninstaller, $this->factory->create(JobComponentUninstall::COMPONENT_MODULE));
    }

    public function testCreateTheme()
    {
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $themeUninstaller = $this->getMock('Magento\Theme\Model\Theme\ThemeUinstaller');
        $objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Theme\Model\Theme\ThemeUninstaller')
            ->willReturn($themeUninstaller);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $this->serviceLocator->expects($this->never())->method($this->anything());
        $this->assertSame($themeUninstaller, $this->factory->create(JobComponentUninstall::COMPONENT_THEME));
    }
}
