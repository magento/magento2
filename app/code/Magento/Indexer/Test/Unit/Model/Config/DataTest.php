<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Config;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Model\Config\Data
     */
    protected $model;

    /**
     * @var \Magento\Framework\Indexer\Config\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cache;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stateCollection;

    /**
     * @var string
     */
    protected $cacheId = 'indexer_config';

    /**
     * @var string
     */
    protected $indexers = ['indexer1' => [], 'indexer3' => []];

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->reader = $this->createPartialMock(\Magento\Framework\Indexer\Config\Reader::class, ['read']);
        $this->cache = $this->getMockForAbstractClass(
            \Magento\Framework\Config\CacheInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['test', 'load', 'save']
        );
        $this->stateCollection = $this->createPartialMock(
            \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection::class,
            ['getItems']
        );
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    public function testConstructorWithCache()
    {
        $serializedData = 'serialized data';
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->willReturn(true);
        $this->cache->expects($this->once())
            ->method('load')
            ->with($this->cacheId)
            ->willReturn($serializedData);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($this->indexers);

        $this->stateCollection->expects($this->never())->method('getItems');

        $this->model = new \Magento\Indexer\Model\Config\Data(
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

        $this->reader->expects($this->once())->method('read')->willReturn($this->indexers);

        $stateExistent = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['getIndexerId', '__wakeup', 'delete']
        );
        $stateExistent->expects($this->once())->method('getIndexerId')->willReturn('indexer1');
        $stateExistent->expects($this->never())->method('delete');

        $stateNonexistent = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['getIndexerId', '__wakeup', 'delete']
        );
        $stateNonexistent->expects($this->once())->method('getIndexerId')->willReturn('indexer2');
        $stateNonexistent->expects($this->once())->method('delete');

        $states = [$stateExistent, $stateNonexistent];

        $this->stateCollection->expects($this->once())->method('getItems')->willReturn($states);

        $this->model = new \Magento\Indexer\Model\Config\Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId,
            $this->serializerMock
        );
    }
}
