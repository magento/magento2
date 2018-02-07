<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Processor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Processor\CleanCache;

class CleanCacheTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * Subject mock
     *
     * @var \Magento\Framework\Indexer\ActionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * Event manager mock
     *
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * Cache mock
     *
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->subjectMock = $this->getMock(\Magento\Indexer\Model\Processor::class, [], [], '', false);
        $this->contextMock = $this->getMock(\Magento\Framework\Indexer\CacheContext::class, [], [], '', false);
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $this->cacheMock = $this->getMock(\Magento\Framework\App\CacheInterface::class, [], [], '', false);
        $this->plugin = new CleanCache(
            $this->contextMock,
            $this->eventManagerMock
        );

        $reflection = new \ReflectionClass(get_class($this->plugin));
        $reflectionProperty = $reflection->getProperty('cache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->plugin, $this->cacheMock);
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
