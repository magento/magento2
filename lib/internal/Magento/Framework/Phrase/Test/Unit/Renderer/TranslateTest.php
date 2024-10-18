<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use Magento\Framework\Phrase\Renderer\MessageFormatter;
use Magento\Framework\Phrase\Renderer\Translate as PhraseRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate;
use Magento\Framework\TranslateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TranslateTest extends TestCase
{
    /**
     * @var Translate|MockObject
     */
    protected $_translator;

    /**
     * @var PhraseRenderer
     */
    protected $_renderer;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var MessageFormatter|MockObject
     */
    private $messageFormatter;

    protected function setUp(): void
    {
        $this->_translator = $this->getMockForAbstractClass(TranslateInterface::class);
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->messageFormatter = $this->getMockBuilder(MessageFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageFormatter
            ->method('render')
            ->willReturnCallback(function ($source) {
                return end($source);
            });

        $objectManagerHelper = new ObjectManager($this);
        $this->_renderer = $objectManagerHelper->getObject(
            PhraseRenderer::class,
            [
                'translator' => $this->_translator,
                'logger' => $this->loggerMock,
                'messageFormatter' => $this->messageFormatter,
            ]
        );
    }

    public function testRenderTextWithoutTranslation()
    {
        $text = 'text';
        $this->_translator->expects($this->once())
            ->method('getData')
            ->willReturn([]);
        $this->assertEquals($text, $this->_renderer->render([$text], []));
    }

    public function testRenderTextWithSingleQuotes()
    {
        $translatedTextInDictionary = "That's translated text";
        $translatedTextInput = 'That\\\'s translated text';
        $translate = 'translate';

        $this->_translator->expects($this->once())
            ->method('getData')
            ->willReturn([$translatedTextInDictionary => $translate]);

        $this->assertEquals($translate, $this->_renderer->render([$translatedTextInput], []));
    }

    public function testRenderWithoutTranslation()
    {
        $translate = "Text with quote \'";
        $this->_translator->expects($this->once())
            ->method('getData')
            ->willReturn([]);
        $this->assertEquals($translate, $this->_renderer->render([$translate], []));
    }

    public function testRenderTextWithDoubleQuotes()
    {
        $translatedTextInDictionary = "That\"s translated text";
        $translatedTextInput = 'That\"s translated text';
        $translate = 'translate';

        $this->_translator->expects($this->once())
            ->method('getData')
            ->willReturn([$translatedTextInDictionary => $translate]);

        $this->assertEquals($translate, $this->_renderer->render([$translatedTextInput], []));
    }

    public function testRenderException()
    {
        $message = 'something went wrong';
        $exception = new \Exception($message);
        $this->messageFormatter->expects($this->never())
            ->method('render');
        $this->_translator->expects($this->once())
            ->method('getData')
            ->willThrowException($exception);

        $this->expectException('Exception');
        $this->expectExceptionMessage($message);
        $this->_renderer->render(['text'], []);
    }
}
