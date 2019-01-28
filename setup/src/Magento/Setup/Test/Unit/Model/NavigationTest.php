<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\Navigation;

class NavigationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Navigation
     */
    private $navigation;

    public function setUp()
    {
        $this->serviceLocatorMock =
            $this->getMockForAbstractClass(\Zend\ServiceManager\ServiceLocatorInterface::class, ['get']);
        $this->serviceLocatorMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with('config')
            ->will($this->returnValue([
                'navInstallerTitles' => [
                    'install' => 'SomeTitle'
                 ],
                'navInstaller' => [
                    ['key1' => 'value1'],
                    ['key2' => 'value2'],
                    ['nav' => 'abc', 'key3' => 'value3'],
                    ['nav' => ''],
                    ['nav' => false],
                    ['main' => 'abc', 'key3' => 'value3'],
                    ['main' => ''],
                    ['main' => false],
                ]
            ]));
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $this->navigation = new Navigation($this->serviceLocatorMock, $this->deploymentConfig);
    }

    public function testGetType()
    {
        $this->assertSame(Navigation::NAV_INSTALLER, $this->navigation->getType());
    }

    public function testGetData()
    {
        $this->assertSame(
            [
                ['key1' => 'value1'],
                ['key2' => 'value2'],
                ['nav' => 'abc', 'key3' => 'value3'],
                ['nav' => ''],
                ['nav' => false],
                ['main' => 'abc', 'key3' => 'value3'],
                ['main' => ''],
                ['main' => false],
            ],
            $this->navigation->getData()
        );
    }

    public function testGetMenuItems()
    {
        $this->assertSame(
            [['nav' => 'abc', 'key3' => 'value3']],
            $this->navigation->getMenuItems()
        );
    }

    public function testGetMainItems()
    {
        $this->assertSame([['main' => 'abc', 'key3' => 'value3']], array_values($this->navigation->getMainItems()));
    }
}
