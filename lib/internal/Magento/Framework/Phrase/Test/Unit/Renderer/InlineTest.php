<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Test\Unit\Renderer;

class InlineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TranslateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var \Magento\Framework\Phrase\Renderer\Inline
     */
    protected $renderer;

    /**
     * @var \Magento\Framework\Translate\Inline\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->translator = $this->createMock(\Magento\Framework\TranslateInterface::class);
        $this->provider = $this->createMock(\Magento\Framework\Translate\Inline\ProviderInterface::class);
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();

        $this->renderer = new \Magento\Framework\Phrase\Renderer\Inline(
            $this->translator,
            $this->provider,
            $this->loggerMock
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

        $inlineTranslate = $this->createMock(\Magento\Framework\Translate\InlineInterface::class);
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

        $inlineTranslate = $this->createMock(\Magento\Framework\Translate\InlineInterface::class);
        $inlineTranslate->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(false));

        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($inlineTranslate));

        $this->assertEquals($text, $this->renderer->render([$text], []));
    }

    public function testRenderException()
    {
        $message = 'something went wrong';
        $exception = new \Exception($message);

        $this->provider->expects($this->once())
            ->method('get')
            ->willThrowException($exception);

        $this->expectException('Exception', $message);
        $this->renderer->render(['text'], []);
    }
}
