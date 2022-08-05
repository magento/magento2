<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\View\Element\Message\Renderer\RendererInterface;
use Magento\Framework\View\Element\Message\Renderer\RenderersPool;
use PHPUnit\Framework\TestCase;

class RenderersPoolTest extends TestCase
{
    public function testGetRenderer()
    {
        $renderers = [
            'renderer_1' => $this->createMock(
                RendererInterface::class
            ),
            'renderer_2' => $this->createMock(
                RendererInterface::class
            ),
            'renderer_3' => $this->createMock(
                RendererInterface::class
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
