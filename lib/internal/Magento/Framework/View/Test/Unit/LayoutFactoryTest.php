<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

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
            ->with($this->equalTo($instance), $this->equalTo($data))
            ->will($this->returnValue($layoutMock));
        $this->assertInstanceOf($instance, $this->layoutFactory->create($data));
    }

    public function testCreateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('stdClass must be an instance of LayoutInterface.');
        $data = ['some' => 'other_data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new \stdClass()));
        $this->layoutFactory->create($data);
    }
}
