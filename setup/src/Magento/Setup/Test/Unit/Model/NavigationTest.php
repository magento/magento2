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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Navigation
     */
    private $navigation;

    protected function setUp(): void
    {
        $this->serviceLocatorMock =
            $this->createMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $this->serviceLocatorMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with('config')
            ->willReturn([
                'navLandingTitles' => [
                    'install' => 'SomeTitle'
                 ],
                'navLanding' => [
                    ['key1' => 'value1'],
                    ['key2' => 'value2'],
                    ['nav' => 'abc', 'key3' => 'value3'],
                    ['nav' => ''],
                    ['nav' => false],
                    ['main' => 'abc', 'key3' => 'value3'],
                    ['main' => ''],
                    ['main' => false],
                ]
            ]);
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $this->navigation = new Navigation($this->serviceLocatorMock, $this->deploymentConfig);
    }

    public function testGetType()
    {
        $this->assertEquals(Navigation::NAV_LANDING, $this->navigation->getType());
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
