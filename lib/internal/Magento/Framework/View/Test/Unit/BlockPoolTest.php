<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use \Magento\Framework\View\BlockPool;

/**
 * Test for view BlockPool model
 */
class BlockPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BlockPool
     */
    protected $blockPool;

    /**
     * Block factory
     * @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockFactory;

    protected function setUp()
    {
        $this->blockFactory = $this->getMockBuilder(\Magento\Framework\View\Element\BlockFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMock();
        $this->blockPool = new BlockPool($this->blockFactory);
    }

    public function testAdd()
    {
        $blockName = 'testName';
        $blockClass = \Magento\Framework\View\Test\Unit\BlockPoolTestBlock::class;
        $arguments = ['key' => 'value'];

        $block = $this->createMock(\Magento\Framework\View\Test\Unit\BlockPoolTestBlock::class);

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('createBlock')
            ->with($blockClass, $arguments)
            ->will($this->returnValue($block));

        $this->assertEquals($this->blockPool, $this->blockPool->add($blockName, $blockClass, $arguments));

        $this->assertEquals([$blockName => $block], $this->blockPool->get());
        $this->assertEquals($block, $this->blockPool->get($blockName));
        $this->assertNull($this->blockPool->get('someWrongName'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid Block class name: NotExistingBlockClass
     */
    public function testAddWithException()
    {
        $this->blockPool->add('BlockPoolTestBlock', 'NotExistingBlockClass');
    }
}
