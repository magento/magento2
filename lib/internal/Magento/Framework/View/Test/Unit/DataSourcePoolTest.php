<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\View\Test\Unit;

use \Magento\Framework\View\DataSourcePool;

/**
 * Test for view Context model
 */
class DataSourcePoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataSourcePool
     */
    protected $dataSourcePool;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockFactory;

    protected function setUp()
    {
        $this->blockFactory = $this->getMockBuilder(\Magento\Framework\View\Element\BlockFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataSourcePool = $objectManager->getObject(
            \Magento\Framework\View\DataSourcePool::class, [
            'blockFactory' => $this->blockFactory
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid Data Source class name: NotExistingBlockClass
     */
    public function testAddWithException()
    {
        $this->dataSourcePool->add('DataSourcePoolTestBlock', 'NotExistingBlockClass');
    }

    /**
     * @param $blockClass
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createBlock($blockClass)
    {
        $block = $this->createMock(\Magento\Framework\View\Element\BlockInterface::class);

        $this->blockFactory->expects($this->once())
            ->method('createBlock')
            ->with($blockClass)
            ->will($this->returnValue($block));
        return $block;
    }

    public function testAdd()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = \Magento\Framework\View\Test\Unit\DataSourcePoolTestBlock::class;

        $block = $this->createBlock($blockClass);

        $this->assertSame($block, $this->dataSourcePool->add($blockName, $blockClass));
    }

    public function testGet()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = \Magento\Framework\View\Test\Unit\DataSourcePoolTestBlock::class;

        $block = $this->createBlock($blockClass);
        $this->dataSourcePool->add($blockName, $blockClass);

        $this->assertSame($block, $this->dataSourcePool->get($blockName));
        $this->assertEquals([$blockName => $block], $this->dataSourcePool->get());
        $this->assertNull($this->dataSourcePool->get('WrongName'));
    }

    public function testGetEmpty()
    {
        $this->assertEquals([], $this->dataSourcePool->get());
    }

    public function testAssignAndGetNamespaceData()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = \Magento\Framework\View\Test\Unit\DataSourcePoolTestBlock::class;

        $block = $this->createBlock($blockClass);
        $this->dataSourcePool->add($blockName, $blockClass);

        $namespace = 'namespace';
        $alias = 'alias';
        $this->dataSourcePool->assign($blockName, $namespace, $alias);

        $this->assertEquals(['alias' => $block], $this->dataSourcePool->getNamespaceData($namespace));
        $this->assertEquals([], $this->dataSourcePool->getNamespaceData('WrongNamespace'));
    }
}
