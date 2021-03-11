<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use \Magento\Framework\Phrase\Renderer\Composite;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Composite
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rendererOne;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rendererTwo;

    protected function setUp(): void
    {
        $this->rendererOne = $this->createMock(\Magento\Framework\Phrase\RendererInterface::class);
        $this->rendererTwo = $this->createMock(\Magento\Framework\Phrase\RendererInterface::class);
        $this->object = new \Magento\Framework\Phrase\Renderer\Composite([$this->rendererOne, $this->rendererTwo]);
    }

    /**
     */
    public function testConstructorException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Instance of the phrase renderer is expected, got stdClass instead');

        new \Magento\Framework\Phrase\Renderer\Composite([new \stdClass()]);
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
