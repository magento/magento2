<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\Navigation;

class NavigationTest extends \PHPUnit_Framework_TestCase
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
            $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
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
        $this->deploymentConfig = $this->getMock(
            'Magento\Framework\App\DeploymentConfig',
            [],
            [],
            '',
            false
        );
        $this->navigation = new Navigation($this->serviceLocatorMock, $this->deploymentConfig);
    }

    public function testGetType()
    {
        $this->assertEquals(Navigation::NAV_INSTALLER, $this->navigation->getType());
    }

    public function testGetData()
    {
        $this->assertEquals(
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
        $this->assertEquals(
            [['nav' => 'abc', 'key3' => 'value3']],
            $this->navigation->getMenuItems()
        );
    }

    public function testGetMainItems()
    {
        $this->assertEquals([['main' => 'abc', 'key3' => 'value3']], array_values($this->navigation->getMainItems()));
    }
}
