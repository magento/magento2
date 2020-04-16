<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Indexer\Config\Reader;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Indexer\Model\Config\Data;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $model;

    /**
     * @var Reader|MockObject
     */
    protected $reader;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cache;

    /**
     * @var Collection|MockObject
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
        $this->stateCollection = $this->createPartialMock(
            Collection::class,
            ['getItems']
        );
        $this->serializerMock = $this->createMock(SerializerInterface::class);
    }

    public function testConstructorWithCache()
    {
        $serializedData = 'serialized data';
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->will($this->returnValue(true));
        $this->cache->expects($this->once())
            ->method('load')
            ->with($this->cacheId)
            ->willReturn($serializedData);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($this->indexers);

        $this->stateCollection->expects($this->never())->method('getItems');

        $this->model = new Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId,
            $this->serializerMock
        );
    }

    public function testConstructorWithoutCache()
    {
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->will($this->returnValue(false));
        $this->cache->expects($this->once())->method('load')->with($this->cacheId)->will($this->returnValue(false));

        $this->reader->expects($this->once())->method('read')->will($this->returnValue($this->indexers));

        $stateExistent = $this->createPartialMock(
            State::class,
            ['getIndexerId', '__wakeup', 'delete']
        );
        $stateExistent->expects($this->once())->method('getIndexerId')->will($this->returnValue('indexer1'));
        $stateExistent->expects($this->never())->method('delete');

        $stateNonexistent = $this->createPartialMock(
            State::class,
            ['getIndexerId', '__wakeup', 'delete']
        );
        $stateNonexistent->expects($this->once())->method('getIndexerId')->will($this->returnValue('indexer2'));
        $stateNonexistent->expects($this->once())->method('delete');

        $states = [$stateExistent, $stateNonexistent];

        $this->stateCollection->expects($this->once())->method('getItems')->will($this->returnValue($states));

        $this->model = new Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId,
            $this->serializerMock
        );
    }
}
