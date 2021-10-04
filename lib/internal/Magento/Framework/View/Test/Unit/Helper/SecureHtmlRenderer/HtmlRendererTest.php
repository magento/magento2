<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Helper\SecureHtmlRenderer;

use Magento\Framework\Escaper;
use Magento\Framework\View\Helper\SecureHtmlRender\HtmlRenderer;
use Magento\Framework\View\Helper\SecureHtmlRender\TagData;
use PHPUnit\Framework\TestCase;

class HtmlRendererTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
    }

    /**
     * @covers \Magento\Framework\View\Helper\SecureHtmlRender\HtmlRenderer::renderTag
     */
    public function testRenderTag()
    {
        $helper = new HtmlRenderer($this->escaperMock);

        /** Test void element to have closing tag */
        $tag = new TagData('hr', [], null, true);
        $this->assertEquals(
            "<hr/>",
            $helper->renderTag($tag)
        );

        /** Test void element to never have content */
        $tag = new TagData('hr', [], 'content', false);
        $this->assertEquals(
            "<hr/>",
            $helper->renderTag($tag)
        );

        /** Test any non-void element to not have a closing tag while not having content */
        $tags = new TagData('script', [], null, false);
        $this->assertEquals(
            "<script></script>",
            $helper->renderTag($tags)
        );

        /** Test any non-void element to not have a closing tag and allow content */
        $tags = new TagData('script', [], 'content', false);
        $this->assertEquals(
            "<script>content</script>",
            $helper->renderTag($tags)
        );
    }
}
