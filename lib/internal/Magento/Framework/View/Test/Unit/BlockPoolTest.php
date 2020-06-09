<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\View\BlockPool;
use Magento\Framework\View\Element\BlockFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for view BlockPool model
 */
class BlockPoolTest extends TestCase
{
    /**
     * @var BlockPool
     */
    protected $blockPool;

    /**
     * Block factory
     * @var BlockFactory|MockObject
     */
    protected $blockFactory;

    protected function setUp(): void
    {
        $this->blockFactory = $this->getMockBuilder(BlockFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMock();
        $this->blockPool = new BlockPool($this->blockFactory);
    }

    public function testAdd()
    {
        $blockName = 'testName';
        $blockClass = BlockPoolTestBlock::class;
        $arguments = ['key' => 'value'];

        $block = $this->createMock(BlockPoolTestBlock::class);

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('createBlock')
            ->with($blockClass, $arguments)
            ->willReturn($block);

        $this->assertEquals($this->blockPool, $this->blockPool->add($blockName, $blockClass, $arguments));

        $this->assertEquals([$blockName => $block], $this->blockPool->get());
        $this->assertEquals($block, $this->blockPool->get($blockName));
        $this->assertNull($this->blockPool->get('someWrongName'));
    }

    public function testAddWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid Block class name: NotExistingBlockClass');
        $this->blockPool->add('BlockPoolTestBlock', 'NotExistingBlockClass');
    }
}
