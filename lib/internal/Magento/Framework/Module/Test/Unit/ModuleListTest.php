<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\ModuleList\Loader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for module list
 */
class ModuleListTest extends TestCase
{
    /**
     * Fixture for all modules' meta-information
     *
     * @var array
     */
    private static $allFixture = ['foo' => ['key' => 'value'], 'bar' => ['another' => 'value']];

    /**
     * Fixture for enabled modules
     *
     * @var array
     */
    private static $enabledFixture = ['foo' => 1, 'bar' => 0];

    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $loader;

    /**
     * @var ModuleList
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(DeploymentConfig::class);
        $this->loader = $this->createMock(Loader::class);
        $this->model = new ModuleList($this->config, $this->loader);
    }

    /**
     * @return void
     */
    public function testGetAll(): void
    {
        $this->setLoadAllExpectation();
        $this->setLoadConfigExpectation();
        $expected = ['foo' => self::$allFixture['foo']];
        $this->assertSame($expected, $this->model->getAll());
        $this->assertSame($expected, $this->model->getAll()); // second time to ensure loadAll is called once
    }

    /**
     * @return void
     */
    public function testGetAllNoData(): void
    {
        $this->loader->expects($this->exactly(2))->method('load')->willReturn([]);
        $this->setLoadConfigExpectation(false);
        $this->assertEquals([], $this->model->getAll());
        $this->assertEquals([], $this->model->getAll());
    }

    /**
     * @return void
     */
    public function testGetOne(): void
    {
        $this->setLoadAllExpectation();
        $this->setLoadConfigExpectation();
        $this->assertSame(['key' => 'value'], $this->model->getOne('foo'));
        $this->assertNull($this->model->getOne('bar'));
    }

    /**
     * @return void
     */
    public function testGetNames(): void
    {
        $this->setLoadAllExpectation(false);
        $this->setLoadConfigExpectation();
        $this->assertSame(['foo'], $this->model->getNames());
        $this->assertSame(['foo'], $this->model->getNames()); // second time to ensure config loader is called once
    }

    /**
     * @return void
     */
    public function testHas(): void
    {
        $this->setLoadAllExpectation(false);
        $this->setLoadConfigExpectation();
        $this->assertTrue($this->model->has('foo'));
        $this->assertFalse($this->model->has('bar'));
    }

    /**
     * @return void
     */
    public function testIsModuleInfoAvailable(): void
    {
        $this->setLoadConfigExpectation(true);
        $this->assertTrue($this->model->isModuleInfoAvailable());
    }

    /**
     * @return void
     */
    public function testIsModuleInfoAvailableNoConfig(): void
    {
        $this->config
            ->method('get')
            ->willReturnOnConsecutiveCalls(['modules' => 'testModule'], null);
        $this->assertFalse($this->model->isModuleInfoAvailable());
    }

    /**
     * Prepares expectation for loading deployment configuration.
     *
     * @param bool $isExpected
     * @return void
     *
     * @return void
     */
    private function setLoadConfigExpectation($isExpected = true): void
    {
        if ($isExpected) {
            $this->config->expects($this->exactly(2))->method('get')->willReturn(self::$enabledFixture);
        } else {
            $this->config->expects($this->never())->method('get');
        }
    }

    /**
     * Prepares expectation for loading full list of modules.
     *
     * @param bool $isExpected
     * @return void
     *
     * @return void
     */
    private function setLoadAllExpectation($isExpected = true): void
    {
        if ($isExpected) {
            $this->loader->expects($this->once())->method('load')->willReturn(self::$allFixture);
        } else {
            $this->loader->expects($this->never())->method('load');
        }
    }
}
