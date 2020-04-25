<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\BlockInterface;

class BlockFactoryTest extends TestCase
{
    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->blockFactory = $objectManagerHelper->getObject(
            BlockFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateBlock()
    {
        $className = Template::class;
        $argumentsResult = ['arg1', 'arg2'];

        $templateMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($templateMock));

        $this->assertInstanceOf(
            BlockInterface::class,
            $this->blockFactory->createBlock($className, $argumentsResult)
        );
    }

    public function testCreateBlockWithException()
    {
        $this->expectException('LogicException');
        $this->blockFactory->createBlock('invalid_class_name');
    }
}
