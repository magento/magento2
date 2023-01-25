<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Console\Response;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * @var MockObject|TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var MockObject|StateInterface
     */
    private $cacheState;

    /**
     * @var MockObject|Response
     */
    private $response;

    /**
     * @var MockObject|FrontendPool
     */
    private $frontendPool;

    /**
     * @var Manager
     */
    private $model;

    protected function setUp(): void
    {
        $this->cacheTypeList = $this->getMockForAbstractClass(TypeListInterface::class);
        $this->cacheState = $this->getMockForAbstractClass(StateInterface::class);
        $this->response = $this->createMock(Response::class);
        $this->frontendPool = $this->createMock(FrontendPool::class);
        $this->model = new Manager($this->cacheTypeList, $this->cacheState, $this->frontendPool);
    }

    public function testEmptyRequest()
    {
        $this->cacheState->expects($this->never())->method('setEnabled');
        $this->cacheState->expects($this->never())->method('persist');
        $this->frontendPool->expects($this->never())->method('get');
        $this->model->setEnabled([], true);
    }

    /**
     * Test setting all cache types to true
     *
     * In this fixture, there are 2 of 3 cache types disabled, but will be enabled
     * so persist() should be invoked once, then clean() for each of those which changed
     */
    public function testSetEnabledTrueAll()
    {
        $caches = ['foo', 'bar', 'baz'];
        $cacheStatusMap = [['foo', true], ['bar', false], ['baz', false]];
        $this->cacheState->expects($this->exactly(3))
            ->method('isEnabled')
            ->willReturnMap($cacheStatusMap);
        $this->cacheState->expects($this->exactly(2))
            ->method('setEnabled')
            ->willReturnMap([['bar', true], ['baz', true]]);
        $this->cacheState->expects($this->once())->method('persist');
        $this->assertEquals(['bar', 'baz'], $this->model->setEnabled($caches, true));
    }

    /**
     * Test setting all cache types to true
     *
     * Fixture is the same as in previous test, but here the intent to disable all of them.
     * Since only one of them is enabled, the setter should be invoked only once.
     * Also the operation of deactivating cache does not need cleanup (it is relevant when you enable it).
     */
    public function testSetEnabledFalseAll()
    {
        $caches = ['foo', 'bar', 'baz'];
        $cacheStatusMap = [['foo', true], ['bar', false], ['baz', false]];
        $this->cacheState->expects($this->exactly(3))
            ->method('isEnabled')
            ->willReturnMap($cacheStatusMap);
        $this->cacheState->expects($this->once())->method('setEnabled')->with('foo', false);
        $this->cacheState->expects($this->once())->method('persist');
        $this->frontendPool->expects($this->never())->method('get');
        $this->assertEquals(['foo'], $this->model->setEnabled($caches, false));
    }

    /**
     * Test flushing all cache types
     *
     * Emulates situation when some cache frontends reuse the same backend
     * Asserts that the flush is invoked only once per affected storage
     */
    public function testFlushAll()
    {
        $cacheTypes = ['foo', 'bar', 'baz'];
        $frontendFoo = $this->getMockForAbstractClass(FrontendInterface::class);
        $frontendBar = $this->getMockForAbstractClass(FrontendInterface::class);
        $frontendBaz = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->frontendPool->expects($this->exactly(3))->method('get')->willReturnMap([
            ['foo', $frontendFoo],
            ['bar', $frontendBar],
            ['baz', $frontendBaz],
        ]);
        $backendOne = $this->getMockForAbstractClass(\Zend_Cache_Backend_Interface::class);
        $backendTwo = $this->getMockForAbstractClass(\Zend_Cache_Backend_Interface::class);
        $frontendFoo->expects($this->once())->method('getBackend')->willReturn($backendOne);
        $frontendBar->expects($this->once())->method('getBackend')->willReturn($backendOne);
        $frontendBaz->expects($this->once())->method('getBackend')->willReturn($backendTwo);
        $backendOne->expects($this->once())->method('clean');
        $backendTwo->expects($this->once())->method('clean');
        $this->model->flush($cacheTypes);
    }

    public function testGetStatus()
    {
        $types = [
            ['id' => 'foo', 'status' => true],
            ['id' => 'bar', 'status' => false],
        ];
        $this->cacheTypeList->expects($this->once())->method('getTypes')->willReturn($types);
        $this->assertSame(['foo' => true, 'bar' => false], $this->model->getStatus());
    }

    public function testGetAvailableTypes()
    {
        $types = [
            ['id' => 'foo', 'status' => true],
            ['id' => 'bar', 'status' => false],
        ];
        $this->cacheTypeList->expects($this->once())->method('getTypes')->willReturn($types);
        $this->assertSame(['foo', 'bar'], $this->model->getAvailableTypes());
    }
}
