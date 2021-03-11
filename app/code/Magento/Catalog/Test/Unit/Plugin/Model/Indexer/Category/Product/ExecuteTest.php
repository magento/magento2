<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\Indexer\Category\Product;

use \Magento\Catalog\Plugin\Model\Indexer\Category\Product\Execute;

class ExecuteTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Plugin\Model\Indexer\Category\Product\Execute */
    protected $execute;

    /** @var \Magento\PageCache\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    /** @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $typeList;

    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMock();
        $this->typeList = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['invalidate'])
            ->getMockForAbstractClass();

        $this->execute = new Execute($this->config, $this->typeList);
    }

    public function testAfterExecute()
    {
        $subject = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Category\Product\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $result = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Category\Product\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->typeList->expects($this->never())
            ->method('invalidate');

        $this->assertEquals(
            $result,
            $this->execute->afterExecute($subject, $result)
        );
    }

    public function testAfterExecuteInvalidate()
    {
        $subject = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Category\Product\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $result = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Category\Product\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->typeList->expects($this->once())
            ->method('invalidate')
            ->with('full_page');

        $this->assertEquals(
            $result,
            $this->execute->afterExecute($subject, $result)
        );
    }
}
