<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;
use Magento\Framework\Phrase\RendererInterface;
use PHPUnit\Framework\TestCase;

class LocalizedExceptionTest extends TestCase
{
    /** @var RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->defaultRenderer = Phrase::getRenderer();
        $rendererMock = $this->getMockBuilder(Placeholder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderedMessage = 'rendered message';
        $rendererMock->expects($this->once())
            ->method('render')
            ->willReturn($this->renderedMessage);
        Phrase::setRenderer($rendererMock);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * @param string $message
     * @param array $params
     * @param string $expectedLogMessage
     * @return void
     * @dataProvider constructorParametersDataProvider
     */
    public function testConstructor($message, $params, $expectedLogMessage)
    {
        $cause = new \Exception();
        $localizeException = new LocalizedException(
            new Phrase($message, $params),
            $cause
        );

        $this->assertEquals(0, $localizeException->getCode());

        $this->assertEquals($message, $localizeException->getRawMessage());
        $this->assertEquals($this->renderedMessage, $localizeException->getMessage());
        $this->assertEquals($expectedLogMessage, $localizeException->getLogMessage());

        $this->assertSame($cause, $localizeException->getPrevious());
    }

    /**
     * @return array
     */
    public function constructorParametersDataProvider()
    {
        return [
            'withNoNameParameters' => [
                'message %1 %2',
                ['parameter1',
                    'parameter2'],
                'message parameter1 parameter2',
            ],
            'withNamedParameters'  => [
                'message %key1 %key2',
                ['key1' => 'parameter1',
                    'key2' => 'parameter2'],
                'message parameter1 parameter2',
            ],
            'withoutParameters'    => [
                'message',
                [],
                'message',
                'message',
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetRawMessage()
    {
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $localizeException = new LocalizedException(
            new Phrase($message, $params),
            $cause
        );
        $this->assertEquals($message, $localizeException->getRawMessage());
    }

    /**
     * @return void
     */
    public function testGetParameters()
    {
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $localizeException = new LocalizedException(
            new Phrase($message, $params),
            $cause
        );

        $this->assertEquals($params, $localizeException->getParameters());
    }

    /**
     * @return void
     */
    public function testGetLogMessage()
    {
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();

        $localizeException = new LocalizedException(
            new Phrase($message, $params),
            $cause
        );
        $expectedLogMessage = 'message parameter1 parameter2';
        $this->assertEquals($expectedLogMessage, $localizeException->getLogMessage());
    }

    public function testGetCode()
    {
        $expectedCode = 42;
        $localizedException = new LocalizedException(
            new Phrase("message %1", ['test']),
            new \Exception(),
            $expectedCode
        );

        $this->assertEquals($expectedCode, $localizedException->getCode());
    }
}
