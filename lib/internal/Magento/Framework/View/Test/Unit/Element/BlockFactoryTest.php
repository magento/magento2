<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

class BlockFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->blockFactory = $objectManagerHelper->getObject(
            \Magento\Framework\View\Element\BlockFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateBlock()
    {
        $className = \Magento\Framework\View\Element\Template::class;
        $argumentsResult = ['arg1', 'arg2'];

        $templateMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->willReturn($templateMock);

        $this->assertInstanceOf(
            \Magento\Framework\View\Element\BlockInterface::class,
            $this->blockFactory->createBlock($className, $argumentsResult)
        );
    }

    /**
     */
    public function testCreateBlockWithException()
    {
        $this->expectException(\LogicException::class);

        $this->blockFactory->createBlock('invalid_class_name');
    }
}
