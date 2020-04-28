<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Report\Framework\Data\Config;
use Magento\Setup\Module\Dependency\Report\Framework\Data\Module;
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

    public function setUp(): void
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
        $this->moduleFirst->expects($this->once())->method('getDependenciesCount')->will($this->returnValue(0));
        $this->moduleSecond->expects($this->once())->method('getDependenciesCount')->will($this->returnValue(2));

        $this->assertEquals(2, $this->config->getDependenciesCount());
    }
}
