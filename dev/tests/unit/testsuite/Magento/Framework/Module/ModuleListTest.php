<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class ModuleListTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loader;

    /**
     * @var ModuleList
     */
    private $model;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->model = new ModuleList($this->config, $this->loader);
    }

    public function testGetAll()
    {
        $this->setLoadAllExpectation();
        $this->setLoadConfigExpectation();
        $expected = ['foo' => self::$allFixture['foo']];
        $this->assertSame($expected, $this->model->getAll());
        $this->assertSame($expected, $this->model->getAll()); // second time to ensure loadAll is called once
    }

    public function testGetAllNoData()
    {
        $this->loader->expects($this->exactly(2))->method('load')->willReturn([]);
        $this->setLoadConfigExpectation(false);
        $this->assertEquals([], $this->model->getAll());
        $this->assertEquals([], $this->model->getAll());
    }

    public function testGetOne()
    {
        $this->setLoadAllExpectation();
        $this->setLoadConfigExpectation();
        $this->assertSame(['key' => 'value'], $this->model->getOne('foo'));
        $this->assertNull($this->model->getOne('bar'));
    }

    public function testGetNames()
    {
        $this->setLoadAllExpectation(false);
        $this->setLoadConfigExpectation();
        $this->assertSame(['foo'], $this->model->getNames());
        $this->assertSame(['foo'], $this->model->getNames()); // second time to ensure config loader is called once
    }

    public function testHas()
    {
        $this->setLoadAllExpectation(false);
        $this->setLoadConfigExpectation();
        $this->assertTrue($this->model->has('foo'));
        $this->assertFalse($this->model->has('bar'));
    }

    /**
     * Prepares expectation for loading deployment configuration
     *
     * @param bool $isExpected
     * @return void
     */
    private function setLoadConfigExpectation($isExpected = true)
    {
        if ($isExpected) {
            $this->config->expects($this->once())->method('getSegment')->willReturn(self::$enabledFixture);
        } else {
            $this->config->expects($this->never())->method('getSegment');
        }
    }

    /**
     * Prepares expectation for loading full list of modules
     *
     * @param bool $isExpected
     * @return void
     */
    private function setLoadAllExpectation($isExpected = true)
    {
        if ($isExpected) {
            $this->loader->expects($this->once())->method('load')->willReturn(self::$allFixture);
        } else {
            $this->loader->expects($this->never())->method('load');
        }
    }
}
