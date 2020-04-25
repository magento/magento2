<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Render;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Render\RenderFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\RenderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RenderFactoryTest extends TestCase
{
    /** @var RenderFactory */
    protected $renderFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->renderFactory = $this->objectManagerHelper->getObject(
            RenderFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testGet()
    {
        $instance = RenderInterface::class;
        $renderMock = $this->createMock($instance);
        $data = 'RenderInterface';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\Render\RenderInterface::class))
            ->will($this->returnValue($renderMock));
        $this->assertInstanceOf($instance, $this->renderFactory->get($data));
    }

    public function testGetException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Type "RenderInterface" is not instance on Magento\Framework\View\RenderInterface'
        );
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\Render\RenderInterface::class))
            ->will($this->returnValue(new \stdClass()));
        $this->renderFactory->get('RenderInterface');
    }
}
