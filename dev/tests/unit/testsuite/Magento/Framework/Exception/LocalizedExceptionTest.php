<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

class LocalizedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Phrase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $phraseMock;

    public function setUp()
    {
        $this->phraseMock = $this->getMockBuilder('Magento\Framework\Phrase')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @var string $message
     * @var array $params
     * @var string $expectedMessage
     * @dataProvider exceptionDataProvider
     */
    public function testException(
        $message,
        $params,
        $expectedMessage
    ) {
        $this->setPhraseExpectations($message, $params, $expectedMessage);
        $cause = new \Exception();
        $exception = new LocalizedException($this->phraseMock, $cause);

        $this->assertEquals(0, $exception->getCode());
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($message, $exception->getRawMessage());
        $this->assertEquals($expectedMessage, $exception->getLogMessage());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function exceptionDataProvider()
    {
        return [
            'withoutParameters' => [
                'message' => 'message',
                'params' => [],
                'expectedMessage' => 'message'
            ],
            'withParameters' => [
                'message' => 'message %1 %2',
                'params' => ['parameter1', 'parameter2'],
                'expectedMessage' => 'message parameter1 parameter2',
            ],
            'withNamedParameters' => [
                'message' => 'message %key1 %key2',
                'params' => ['key1' => 'parameter1', 'key2' => 'parameter2'],
                'expectedMessage' => 'message parameter1 parameter2',
            ]
        ];
    }

    /**
     * @param string $text
     * @param array $arguments
     * @param string $renderResult
     */
    protected function setPhraseExpectations($text, $arguments, $renderResult)
    {
        $this->phraseMock->expects($this->any())
            ->method('getText')
            ->willReturn($text);
        $this->phraseMock->expects($this->any())
            ->method('getArguments')
            ->willReturn($arguments);
        $this->phraseMock->expects($this->any())
            ->method('render')
            ->willReturn($renderResult);
    }
}
