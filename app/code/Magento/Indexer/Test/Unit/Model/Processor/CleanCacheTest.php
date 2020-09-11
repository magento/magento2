<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Processor;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Processor;
use Magento\Indexer\Model\Processor\CleanCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CleanCacheTest extends TestCase
{
    /**
     * Tested plugin
     *
     * @var CleanCache
     */
    protected $plugin;

    /**
     * Mock for context
     *
     * @var CacheContext|MockObject
     */
    protected $contextMock;

    /**
     * Subject mock
     *
     * @var ActionInterface|MockObject
     */
    protected $subjectMock;

    /**
     * Event manager mock
     *
     * @var Manager|MockObject
     */
    protected $eventManagerMock;

    /**
     * Cache mock
     *
     * @var CacheInterface|MockObject
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
        $this->subjectMock = $this->createMock(Processor::class);
        $this->contextMock = $this->createMock(CacheContext::class);
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
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
                'clean_cache_after_reindex',
                ['object' => $this->contextMock]
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
