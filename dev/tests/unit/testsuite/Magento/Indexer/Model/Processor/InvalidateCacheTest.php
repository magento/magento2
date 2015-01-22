<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\Processor;

class InvalidateCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Processor\InvalidateCache
     */
    protected $plugin;

    /**
     * @var \Magento\Indexer\Model\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Indexer\Model\ActionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Indexer\Model\Processor',
            [], [], '', false);
        $this->contextMock = $this->getMock('Magento\Indexer\Model\CacheContext',
            [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager',
            [], [], '', false);
        $this->moduleManager = $this->getMock('Magento\Framework\Module\Manager',
            [], [], '', false);
        $this->plugin = new \Magento\Indexer\Model\Processor\InvalidateCache(
            $this->contextMock, $this->eventManagerMock, $this->moduleManager);
    }

    /**
     * Test afterUpdateMview with enabled PageCache module
     */
    public function testAfterUpdateMviewPageCacheEnabled()
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('clean_cache_after_reindex'),
                $this->equalTo(['object' => $this->contextMock]));
        $actualResult = $this->plugin->afterUpdateMview($this->subjectMock);
        $this->assertNull($actualResult);
    }

    /**
     * afterUpdateMview with disabled PageCache module
     */
    public function testAfterUpdateMviewPageCacheDisabled()
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(false));
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch');
        $actualResult = $this->plugin->afterUpdateMview($this->subjectMock);
        $this->assertNull($actualResult);
    }
}
