<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleSecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Data\Config
     */
    protected $config;

    public function setUp()
    {
        $this->moduleFirst = $this->getMock(
            \Magento\Setup\Module\Dependency\Report\Framework\Data\Module::class,
            [],
            [],
            '',
            false
        );
        $this->moduleSecond = $this->getMock(
            \Magento\Setup\Module\Dependency\Report\Framework\Data\Module::class,
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Framework\Data\Config::class,
            ['modules' => [$this->moduleFirst, $this->moduleSecond]]
        );
    }

    public function testGetDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getDependenciesCount')->will($this->returnValue(0));
        $this->moduleSecond->expects($this->once())->method('getDependenciesCount')->will($this->returnValue(2));

        $this->assertEquals(2, $this->config->getDependenciesCount());
    }
}
