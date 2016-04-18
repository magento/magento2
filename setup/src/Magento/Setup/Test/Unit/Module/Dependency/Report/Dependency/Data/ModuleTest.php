<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Dependency\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dependencyFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dependencySecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module
     */
    protected $module;

    public function setUp()
    {
        $this->dependencyFirst = $this->getMock(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency',
            [],
            [],
            '',
            false
        );
        $this->dependencySecond = $this->getMock(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency',
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManager($this);
        $this->module = $objectManagerHelper->getObject(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Module',
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
        $this->dependencyFirst->expects($this->once())->method('isHard')->will($this->returnValue(true));
        $this->dependencyFirst->expects($this->never())->method('isSoft');

        $this->dependencySecond->expects($this->once())->method('isHard')->will($this->returnValue(false));
        $this->dependencySecond->expects($this->never())->method('isSoft');

        $this->assertEquals(1, $this->module->getHardDependenciesCount());
    }

    public function testGetSoftDependenciesCount()
    {
        $this->dependencyFirst->expects($this->never())->method('isHard');
        $this->dependencyFirst->expects($this->once())->method('isSoft')->will($this->returnValue(true));

        $this->dependencySecond->expects($this->never())->method('isHard');
        $this->dependencySecond->expects($this->once())->method('isSoft')->will($this->returnValue(false));

        $this->assertEquals(1, $this->module->getSoftDependenciesCount());
    }
}
