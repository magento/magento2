<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

class InlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translator;

    /**
     * @var \Magento\Framework\Phrase\Renderer\Translate
     */
    protected $_renderer;

    /**
     * @var \Magento\Framework\Translate\Inline\ProviderInterface
     */
    protected $provider;

    protected function setUp()
    {
        $this->translator = $this->getMock('Magento\Framework\TranslateInterface', [], [], '', false);
        $this->provider = $this->getMock('Magento\Framework\Translate\Inline\ProviderInterface', [], [], '', false);

        $this->renderer = new \Magento\Framework\Phrase\Renderer\Inline(
            $this->translator,
            $this->provider
        );
    }

    public function testRenderIfInlineTranslationIsAllowed()
    {
        $theme = 'theme';
        $text = 'test';
        $result = sprintf('{{{%s}}{{%s}}}', $text, $theme);

        $this->translator->expects($this->once())
            ->method('getTheme')
            ->will($this->returnValue($theme));

        $inlineTranslate = $this->getMock('Magento\Framework\Translate\InlineInterface', [], [], '', []);
        $inlineTranslate->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($inlineTranslate));

        $this->assertEquals($result, $this->renderer->render([$text], []));
    }

    public function testRenderIfInlineTranslationIsNotAllowed()
    {
        $text = 'test';

        $inlineTranslate = $this->getMock('Magento\Framework\Translate\InlineInterface', [], [], '', []);
        $inlineTranslate->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(false));

        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($inlineTranslate));

        $this->assertEquals($text, $this->renderer->render([$text], []));
    }
}
