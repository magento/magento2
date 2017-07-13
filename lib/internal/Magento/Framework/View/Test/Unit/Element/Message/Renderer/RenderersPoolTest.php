<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\View\Element\Message\Renderer\RenderersPool;

class RenderersPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRenderer()
    {
        $renderers = [
            'renderer_1' => $this->getMock(
                \Magento\Framework\View\Element\Message\Renderer\RendererInterface::class
            ),
            'renderer_2' => $this->getMock(
                \Magento\Framework\View\Element\Message\Renderer\RendererInterface::class
            ),
            'renderer_3' => $this->getMock(
                \Magento\Framework\View\Element\Message\Renderer\RendererInterface::class
            )
        ];

        $expectationMap = [
            'renderer_1' => $renderers['renderer_1'],
            'renderer_2' => $renderers['renderer_2'],
            'renderer_3' => $renderers['renderer_3'],
            'renderer_4' => null,
        ];

        $pool = new RenderersPool($renderers);

        foreach ($expectationMap as $code => $renderer) {
            static::assertSame($renderer, $pool->get($code));
        }
    }
}
