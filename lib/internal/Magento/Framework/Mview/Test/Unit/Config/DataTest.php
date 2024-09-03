<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Mview\Config\Data;
use Magento\Framework\Mview\Config\Reader;
use Magento\Framework\Mview\View\State\CollectionInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $config;

    /**
     * @var Reader|MockObject
     */
    private $reader;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var CollectionInterface|MockObject
     */
    private $stateCollection;

    /**
     * @var string
     */
    private $cacheId = 'mview_config';

    /**
     * @var string
     */
    private $views = ['view1' => [], 'view3' => []];

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->reader = $this->createPartialMock(Reader::class, ['read']);
        $this->cache = $this->getMockForAbstractClass(
            CacheInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['test', 'load', 'save']
        );
        $this->stateCollection = $this->getMockForAbstractClass(
            CollectionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getItems']
        );

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
    }

    public function testConstructorWithCache()
    {
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->willReturn(true);
        $this->cache->expects($this->once())
            ->method('load')
            ->with($this->cacheId);

        $this->stateCollection->expects($this->never())->method('getItems');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($this->views);

        $this->config = new Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId,
            $this->serializerMock
        );
    }

    public function testConstructorWithoutCache()
    {
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->willReturn(false);
        $this->cache->expects($this->once())->method('load')->with($this->cacheId)->willReturn(false);

        $this->reader->expects($this->once())->method('read')->willReturn($this->views);

        $stateExistent = $this->getMockBuilder(StateInterface::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getViewId', 'delete'])
            ->getMockForAbstractClass();
        $stateExistent->expects($this->once())->method('getViewId')->willReturn('view1');
        $stateExistent->expects($this->never())->method('delete');

        $stateNonexistent = $this->getMockBuilder(StateInterface::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getViewId', 'delete'])
            ->getMockForAbstractClass();
        $stateNonexistent->expects($this->once())->method('getViewId')->willReturn('view2');
        $stateNonexistent->expects($this->once())->method('delete');

        $states = [$stateExistent, $stateNonexistent];

        $this->stateCollection->expects($this->once())->method('getItems')->willReturn($states);

        $this->config = new Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId,
            $this->serializerMock
        );
    }
}
