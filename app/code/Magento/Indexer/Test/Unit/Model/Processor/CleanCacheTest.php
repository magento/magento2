<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Processor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Processor\CleanCache;

class CleanCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tested plugin
     *
     * @var \Magento\Indexer\Model\Processor\CleanCache
     */
    protected $plugin;

    /**
     * Mock for context
     *
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * Subject mock
     *
     * @var \Magento\Framework\Indexer\ActionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * Event manager mock
     *
     * @var \Magento\Framework\Event\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * Cache mock
     *
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->subjectMock = $this->createMock(\Magento\Indexer\Model\Processor::class);
        $this->contextMock = $this->createMock(\Magento\Framework\Indexer\CacheContext::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $this->cacheMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $this->plugin = new CleanCache(
            $this->contextMock,
            $this->eventManagerMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->plugin,
            'cache',
            $this->cacheMock
        );
    }

    /**
     * Test afterUpdateMview
     *
     * @return void
     */
    public function testAfterUpdateMview()
    {
        $tags = ['tag_name1', 'tag_name2'];
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('clean_cache_after_reindex'),
                $this->equalTo(['object' => $this->contextMock])
            );

        $this->contextMock->expects($this->atLeastOnce())
            ->method('getIdentities')
            ->willReturn($tags);

        $this->cacheMock->expects($this->once())
            ->method('clean')
            ->with($tags);

        $this->plugin->afterUpdateMview($this->subjectMock);
    }
}
