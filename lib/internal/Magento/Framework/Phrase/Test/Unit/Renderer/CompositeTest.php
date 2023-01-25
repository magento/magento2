<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use Magento\Framework\Phrase\Renderer\Composite;
use Magento\Framework\Phrase\RendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /**
     * @var Composite
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $rendererOne;

    /**
     * @var MockObject
     */
    protected $rendererTwo;

    protected function setUp(): void
    {
        $this->rendererOne = $this->getMockForAbstractClass(RendererInterface::class);
        $this->rendererTwo = $this->getMockForAbstractClass(RendererInterface::class);
        $this->object = new Composite([$this->rendererOne, $this->rendererTwo]);
    }

    public function testConstructorException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Instance of the phrase renderer is expected, got stdClass instead');
        new Composite([new \stdClass()]);
    }

    public function testRender()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $resultAfterFirst = 'rendered text first';
        $resultAfterSecond = 'rendered text second';

        $this->rendererOne->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            [$text],
            $arguments
        )->willReturn(
            $resultAfterFirst
        );

        $this->rendererTwo->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            [
                $text,
                $resultAfterFirst,
            ],
            $arguments
        )->willReturn(
            $resultAfterSecond
        );

        $this->assertEquals($resultAfterSecond, $this->object->render([$text], $arguments));
    }

    public function testRenderException()
    {
        $message = 'something went wrong';
        $exception = new \Exception($message);

        $this->rendererOne->expects($this->once())
            ->method('render')
            ->willThrowException($exception);

        $this->expectException('Exception');
        $this->expectExceptionMessage($message);
        $this->object->render(['text'], []);
    }
}
