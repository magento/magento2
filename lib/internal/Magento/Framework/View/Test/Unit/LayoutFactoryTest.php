<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutFactoryTest extends TestCase
{
    /** @var LayoutFactory */
    protected $layoutFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->layoutFactory = $this->objectManagerHelper->getObject(
            LayoutFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $instance = LayoutInterface::class;
        $layoutMock = $this->createMock($instance);
        $data = ['some' => 'data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($instance, $data)
            ->willReturn($layoutMock);
        $this->assertInstanceOf($instance, $this->layoutFactory->create($data));
    }

    public function testCreateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('stdClass must be an instance of LayoutInterface.');
        $data = ['some' => 'other_data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(new \stdClass());
        $this->layoutFactory->create($data);
    }
}
