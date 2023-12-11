<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Dependency\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Report\Dependency\Data\Config;
use Magento\Setup\Module\Dependency\Report\Dependency\Data\Module;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Module|MockObject
     */
    protected $moduleFirst;

    /**
     * @var Module|MockObject
     */
    protected $moduleSecond;

    /**
     * @var Config
     */
    protected $config;

    protected function setUp(): void
    {
        $this->moduleFirst = $this->createMock(Module::class);
        $this->moduleSecond = $this->createMock(Module::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            Config::class,
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
