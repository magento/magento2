<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Circular\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Report\Circular\Data\Config;
use Magento\Setup\Module\Dependency\Report\Circular\Data\Module;
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
        $this->moduleFirst->expects($this->once())->method('getChainsCount')->willReturn(0);
        $this->moduleSecond->expects($this->once())->method('getChainsCount')->willReturn(2);

        $this->assertEquals(2, $this->config->getDependenciesCount());
    }
}
