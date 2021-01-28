<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Dependency\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleSecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Config
     */
    protected $config;

    protected function setUp(): void
    {
        $this->moduleFirst = $this->createMock(\Magento\Setup\Module\Dependency\Report\Dependency\Data\Module::class);
        $this->moduleSecond = $this->createMock(\Magento\Setup\Module\Dependency\Report\Dependency\Data\Module::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Dependency\Data\Config::class,
            ['modules' => [$this->moduleFirst, $this->moduleSecond]]
        );
    }

    public function testGetDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getHardDependenciesCount')->willReturn(1);
        $this->moduleFirst->expects($this->once())->method('getSoftDependenciesCount')->willReturn(2);

        $this->moduleSecond->expects($this->once())->method('getHardDependenciesCount')->willReturn(3);
        $this->moduleSecond->expects($this->once())->method('getSoftDependenciesCount')->willReturn(4);

        $this->assertEquals(10, $this->config->getDependenciesCount());
    }

    public function testGetHardDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getHardDependenciesCount')->willReturn(1);
        $this->moduleFirst->expects($this->never())->method('getSoftDependenciesCount');

        $this->moduleSecond->expects($this->once())->method('getHardDependenciesCount')->willReturn(2);
        $this->moduleSecond->expects($this->never())->method('getSoftDependenciesCount');

        $this->assertEquals(3, $this->config->getHardDependenciesCount());
    }

    public function testGetSoftDependenciesCount()
    {
        $this->moduleFirst->expects($this->never())->method('getHardDependenciesCount');
        $this->moduleFirst->expects($this->once())->method('getSoftDependenciesCount')->willReturn(1);

        $this->moduleSecond->expects($this->never())->method('getHardDependenciesCount');
        $this->moduleSecond->expects($this->once())->method('getSoftDependenciesCount')->willReturn(3);

        $this->assertEquals(4, $this->config->getSoftDependenciesCount());
    }
}
