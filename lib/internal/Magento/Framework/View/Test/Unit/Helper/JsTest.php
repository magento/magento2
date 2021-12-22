<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Helper;

use Magento\Framework\View\Helper\Js;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DataObject;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class JsTest extends TestCase
{
    /**
     * @covers \Magento\Framework\View\Helper\Js::getScript
     */
    public function testGetScript()
    {
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attributes, string $content): string {
                    $attributes = new DataObject($attributes);

                    return "<$tag {$attributes->serialize()}>$content</$tag>";
                }
            );
        $helper = new Js($secureRendererMock);
        $this->assertEquals(
            "<script >//<![CDATA[\ntest\n//]]></script>",
            $helper->getScript('test')
        );
    }
}
