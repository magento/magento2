<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Helper;

use Magento\Framework\View\Helper\SecureHtmlRender\TagData;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for the secure HTML helper.
 */
class SecureHtmlRendererTest extends TestCase
{
    /**
     * @var SecureHtmlRenderer
     */
    private $helper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        //Clearing the processors list to ensure stable results.
        $this->helper = $objectManager->create(SecureHtmlRenderer::class, ['processors' => []]);
    }

    /**
     * Provides tags to render.
     *
     * @return array
     */
    public function getTags(): array
    {
        return [
            [
                new TagData('div', ['style' => 'display: none;', 'width' => '20px'], 'some <text>', true),
                '<div style="display&#x3A;&#x20;none&#x3B;" width="20px">some &lt;text&gt;</div>'
            ],
            [
                new TagData('div', [], 'some <b>HTML</b>', false),
                '<div>some <b>HTML</b></div>'
            ],
            [
                new TagData('img', ['src' => 'https://magento.com/img.jpg'], null, true),
                '<img src="https&#x3A;&#x2F;&#x2F;magento.com&#x2F;img.jpg"/>'
            ]
        ];
    }

    /**
     * Test tag rendering.
     *
     * @param TagData $tagData
     * @param string $expected Expected HTML.
     * @return void
     * @dataProvider getTags
     */
    public function testRenderTag(TagData $tagData, string $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->helper->renderTag(
                $tagData->getTag(),
                $tagData->getAttributes(),
                $tagData->getContent(),
                $tagData->isTextContent()
            )
        );
    }

    /**
     * Test rendering an event listener.
     *
     * @return void
     */
    public function testRenderEventHandler(): void
    {
        $this->assertEquals(
            'onclick="alert&#x28;this.parent.getAttribute&#x28;&quot;data-title&quot;&#x29;&#x29;"',
            $this->helper->renderEventListener('onclick', 'alert(this.parent.getAttribute("data-title"))')
        );
    }

    /**
     * Test rendering JS listeners as separate tags.
     *
     * @return void
     */
    public function testRenderEventListenerAsTag(): void
    {
        $html = $this->helper->renderEventListenerAsTag('onclick', 'alert(1)', '#id');
        $this->assertStringContainsString('alert(1)', $html);
        $this->assertStringContainsString('#id', $html);
        $this->assertStringContainsString('click', $html);
    }

    /**
     * Check handler validation
     *
     * @return void
     */
    public function testInvalidEventListener(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->helper->renderEventListenerAsTag('nonevent', '', '');
    }

    /**
     * Test rendering "style" attribute as separate tag.
     *
     * @return void
     */
    public function testRenderStyleAsTag(): void
    {
        $html = $this->helper->renderStyleAsTag('display: none; font-size: 3em;  ', '#id');
        $this->assertStringContainsString('#id', $html);
        $this->assertStringContainsString('display', $html);
        $this->assertStringContainsString('none', $html);
        $this->assertStringContainsString('fontSize', $html);
        $this->assertStringContainsString('3em', $html);
    }

    /**
     * Check style validation
     *
     * @return void
     */
    public function testInvalidStyle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->helper->renderStyleAsTag('display;', '');
    }
}
