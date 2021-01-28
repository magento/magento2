<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Render;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RenderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\View\Render\RenderFactory */
    protected $renderFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->renderFactory = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Render\RenderFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testGet()
    {
        $instance = \Magento\Framework\View\RenderInterface::class;
        $renderMock = $this->createMock($instance);
        $data = 'RenderInterface';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\Render\RenderInterface::class))
            ->willReturn($renderMock);
        $this->assertInstanceOf($instance, $this->renderFactory->get($data));
    }

    /**
     */
    public function testGetException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type "RenderInterface" is not instance on Magento\\Framework\\View\\RenderInterface');

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\Render\RenderInterface::class))
            ->willReturn(new \stdClass());
        $this->renderFactory->get('RenderInterface');
    }
}
