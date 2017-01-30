<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Unit\Model\Processor;

/**
 * Class InvalidateCacheTest
 * @deprecated
 */
class InvalidateCacheTest extends \PHPUnit_Framework_TestCase
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
     * Module manager mock
     *
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Indexer\Model\Processor', [], [], '', false);
        $this->contextMock = $this->getMock('Magento\Framework\Indexer\CacheContext', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->moduleManager = $this->getMock('Magento\Framework\Module\Manager', [], [], '', false);
        $this->plugin = new \Magento\Indexer\Model\Processor\InvalidateCache(
            $this->contextMock,
            $this->eventManagerMock,
            $this->moduleManager
        );
    }

    /**
     * Test afterUpdateMview with enabled PageCache module
     *
     * @return void
     */
    public function testAfterUpdateMviewPageCacheEnabled()
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('clean_cache_after_reindex'),
                $this->equalTo(['object' => $this->contextMock])
            );
        $this->plugin->afterUpdateMview($this->subjectMock);
    }

    /**
     * Test afterUpdateMview with disabled PageCache module
     *
     * @return void
     */
    public function testAfterUpdateMviewPageCacheDisabled()
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(false));
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch');
        $this->plugin->afterUpdateMview($this->subjectMock);
    }
}
