<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product;

class AffectCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Indexer\Product\RefreshPlugin
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
     *  Set up
     */
    public function setUp()
    {
        $this->subjectMock = $this->getMockForAbstractClass('Magento\Indexer\Model\ActionInterface',
            [], '', false, true, true, []);
        $this->contextMock = $this->getMock('Magento\Indexer\Model\CacheContext',
            [], [], '', false);
        $this->plugin = new \Magento\Catalog\Model\Indexer\Product\AffectCache($this->contextMock);
    }

    /**
     * test beforeExecute
     */
    public function testBeforeExecute()
    {
        $expectedIds = [1, 2, 3];
        $this->contextMock->expects($this->once())
            ->method('registerEntities')
            ->with($this->equalTo(\Magento\Catalog\Model\Product::ENTITY),
                $this->equalTo($expectedIds))
            ->will($this->returnValue($this->contextMock));
        $actualIds = $this->plugin->beforeExecute($this->subjectMock, $expectedIds);
        $this->assertEquals([$expectedIds], $actualIds);
    }
}
