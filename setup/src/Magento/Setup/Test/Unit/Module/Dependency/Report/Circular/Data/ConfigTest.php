<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Circular\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Circular\Data\Module|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Circular\Data\Module|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleSecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Circular\Data\Config
     */
    protected $config;

    protected function setUp(): void
    {
        $this->moduleFirst = $this->createMock(\Magento\Setup\Module\Dependency\Report\Circular\Data\Module::class);
        $this->moduleSecond = $this->createMock(\Magento\Setup\Module\Dependency\Report\Circular\Data\Module::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Circular\Data\Config::class,
            ['modules' => [$this->moduleFirst, $this->moduleSecond]]
        );
    }

    public function testGetDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getChainsCount')->willReturn(0);
        $this->moduleSecond->expects($this->once())->method('getChainsCount')->willReturn(2);

        $this->assertEquals(2, $this->config->getDependenciesCount());
    }
}
