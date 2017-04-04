<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Test\Unit\Renderer;

class TranslateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Translate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_translator;

    /**
     * @var \Magento\Framework\Phrase\Renderer\Translate
     */
    protected $_renderer;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->_translator = $this->getMock(\Magento\Framework\TranslateInterface::class, [], [], '', false);
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_renderer = $objectManagerHelper->getObject(
            \Magento\Framework\Phrase\Renderer\Translate::class,
            [
                'translator' => $this->_translator,
                'logger' => $this->loggerMock
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
            ->will($this->returnValue([$translatedTextInDictionary => $translate]));

        $this->assertEquals($translate, $this->_renderer->render([$translatedTextInput], []));
    }

    public function testRenderWithoutTranslation()
    {
        $translate = "Text with quote \'";
        $this->_translator->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([]));
        $this->assertEquals($translate, $this->_renderer->render([$translate], []));
    }

    public function testRenderTextWithDoubleQuotes()
    {
        $translatedTextInDictionary = "That\"s translated text";
        $translatedTextInput = 'That\"s translated text';
        $translate = 'translate';

        $this->_translator->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([$translatedTextInDictionary => $translate]));

        $this->assertEquals($translate, $this->_renderer->render([$translatedTextInput], []));
    }

    public function testRenderException()
    {
        $message = 'something went wrong';
        $exception = new \Exception($message);

        $this->_translator->expects($this->once())
            ->method('getData')
            ->willThrowException($exception);

        $this->setExpectedException('Exception', $message);
        $this->_renderer->render(['text'], []);
    }
}
