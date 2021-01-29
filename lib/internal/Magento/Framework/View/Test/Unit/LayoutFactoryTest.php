<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LayoutFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\View\LayoutFactory */
    protected $layoutFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->layoutFactory = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\LayoutFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $instance = \Magento\Framework\View\LayoutInterface::class;
        $layoutMock = $this->createMock($instance);
        $data = ['some' => 'data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($instance), $this->equalTo($data))
            ->willReturn($layoutMock);
        $this->assertInstanceOf($instance, $this->layoutFactory->create($data));
    }

    /**
     */
    public function testCreateException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stdClass must be an instance of LayoutInterface.');

        $data = ['some' => 'other_data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(new \stdClass());
        $this->layoutFactory->create($data);
    }
}
