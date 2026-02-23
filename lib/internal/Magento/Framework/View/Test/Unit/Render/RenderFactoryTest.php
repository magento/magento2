<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Render;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Render\RenderFactory;
use Magento\Framework\View\RenderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
            ->with(\Magento\Framework\View\Render\RenderInterface::class)
            ->willReturn($renderMock);
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
            ->with(\Magento\Framework\View\Render\RenderInterface::class)
            ->willReturn(new \stdClass());
        $this->renderFactory->get('RenderInterface');
    }
}
