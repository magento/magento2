<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Dependency\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleSecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Config
     */
    protected $config;

    public function setUp()
    {
        $this->moduleFirst = $this->getMock(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Module',
            [],
            [],
            '',
            false
        );
        $this->moduleSecond = $this->getMock(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Module',
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Config',
            ['modules' => [$this->moduleFirst, $this->moduleSecond]]
        );
    }

    public function testGetDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getHardDependenciesCount')->will($this->returnValue(1));
        $this->moduleFirst->expects($this->once())->method('getSoftDependenciesCount')->will($this->returnValue(2));

        $this->moduleSecond->expects($this->once())->method('getHardDependenciesCount')->will($this->returnValue(3));
        $this->moduleSecond->expects($this->once())->method('getSoftDependenciesCount')->will($this->returnValue(4));

        $this->assertEquals(10, $this->config->getDependenciesCount());
    }

    public function testGetHardDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getHardDependenciesCount')->will($this->returnValue(1));
        $this->moduleFirst->expects($this->never())->method('getSoftDependenciesCount');

        $this->moduleSecond->expects($this->once())->method('getHardDependenciesCount')->will($this->returnValue(2));
        $this->moduleSecond->expects($this->never())->method('getSoftDependenciesCount');

        $this->assertEquals(3, $this->config->getHardDependenciesCount());
    }

    public function testGetSoftDependenciesCount()
    {
        $this->moduleFirst->expects($this->never())->method('getHardDependenciesCount');
        $this->moduleFirst->expects($this->once())->method('getSoftDependenciesCount')->will($this->returnValue(1));

        $this->moduleSecond->expects($this->never())->method('getHardDependenciesCount');
        $this->moduleSecond->expects($this->once())->method('getSoftDependenciesCount')->will($this->returnValue(3));

        $this->assertEquals(4, $this->config->getSoftDependenciesCount());
    }
}
