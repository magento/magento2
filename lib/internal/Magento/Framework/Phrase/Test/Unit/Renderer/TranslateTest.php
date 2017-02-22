<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $this->_translator = $this->getMock('Magento\Framework\TranslateInterface', [], [], '', false);
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_renderer = $objectManagerHelper->getObject(
            'Magento\Framework\Phrase\Renderer\Translate',
            [
                'translator' => $this->_translator,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testRender()
    {
        $text = 'text';
        $translatedText = 'translated text';
        $translate = 'translate';

        $this->_translator->expects($this->exactly(2))
            ->method('getData')
            ->will($this->returnValue([$translatedText => $translate]));

        $this->assertEquals($translate, $this->_renderer->render([$translatedText], []));
        $this->assertEquals($text, $this->_renderer->render([$text], []));
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
