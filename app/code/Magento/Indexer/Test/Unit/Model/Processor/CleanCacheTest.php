<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Processor;

class CleanCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested plugin
     *
     * @var \Magento\Indexer\Model\Processor\InvalidateCache
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
     * Set up
     */
    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Indexer\Model\Processor', [], [], '', false);
        $this->contextMock = $this->getMock('Magento\Framework\Indexer\CacheContext', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->plugin = new \Magento\Indexer\Model\Processor\CleanCache(
            $this->contextMock,
            $this->eventManagerMock
        );
    }

    /**
     * Test afterUpdateMview
     *
     * @return void
     */
    public function testAfterUpdateMview()
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('clean_cache_after_reindex'),
                $this->equalTo(['object' => $this->contextMock])
            );
        $this->plugin->afterUpdateMview($this->subjectMock);
    }
}
