<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Dependency\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ModuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dependencyFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dependencySecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module
     */
    protected $module;

    protected function setUp(): void
    {
        $this->dependencyFirst =
            $this->createMock(\Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency::class);
        $this->dependencySecond =
            $this->createMock(\Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->module = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module::class,
            ['name' => 'name', 'dependencies' => [$this->dependencyFirst, $this->dependencySecond]]
        );
    }

    public function testGetName()
    {
        $this->assertEquals('name', $this->module->getName());
    }

    public function testGetDependencies()
    {
        $this->assertEquals([$this->dependencyFirst, $this->dependencySecond], $this->module->getDependencies());
    }

    public function testGetDependenciesCount()
    {
        $this->assertEquals(2, $this->module->getDependenciesCount());
    }

    public function testGetHardDependenciesCount()
    {
        $this->dependencyFirst->expects($this->once())->method('isHard')->willReturn(true);
        $this->dependencyFirst->expects($this->never())->method('isSoft');

        $this->dependencySecond->expects($this->once())->method('isHard')->willReturn(false);
        $this->dependencySecond->expects($this->never())->method('isSoft');

        $this->assertEquals(1, $this->module->getHardDependenciesCount());
    }

    public function testGetSoftDependenciesCount()
    {
        $this->dependencyFirst->expects($this->never())->method('isHard');
        $this->dependencyFirst->expects($this->once())->method('isSoft')->willReturn(true);

        $this->dependencySecond->expects($this->never())->method('isHard');
        $this->dependencySecond->expects($this->once())->method('isSoft')->willReturn(false);

        $this->assertEquals(1, $this->module->getSoftDependenciesCount());
    }
}
