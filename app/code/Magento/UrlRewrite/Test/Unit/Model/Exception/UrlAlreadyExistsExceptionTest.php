<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\Exception;

use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Class UrlAlreadyExistsExceptionTest
 */
class UrlAlreadyExistsExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Phrase\RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $rendererMock = $this->getMockBuilder(\Magento\Framework\Phrase\Renderer\Placeholder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderedMessage = 'rendered message';
        $rendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($this->renderedMessage));
        \Magento\Framework\Phrase::setRenderer($rendererMock);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
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
        $localizeException = new UrlAlreadyExistsException(
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
        $localizeException = new UrlAlreadyExistsException(
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
        $localizeException = new UrlAlreadyExistsException(
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

        $localizeException = new UrlAlreadyExistsException(
            new Phrase($message, $params),
            $cause
        );
        $expectedLogMessage = 'message parameter1 parameter2';
        $this->assertEquals($expectedLogMessage, $localizeException->getLogMessage());
    }

    public function testGetCode()
    {
        $expectedCode = 42;
        $localizedException = new UrlAlreadyExistsException(
            new Phrase("message %1", ['test']),
            new \Exception(),
            $expectedCode
        );

        $this->assertEquals($expectedCode, $localizedException->getCode());
    }

    public function testUrls()
    {
        $expectedCode = 42;
        $urls = ['someUrl.html'];
        $localizedException = new UrlAlreadyExistsException(
            new Phrase("message %1", ['test']),
            new \Exception(),
            $expectedCode,
            $urls
        );

        $this->assertEquals($urls, $localizedException->getUrls());
    }

    public function testDefaultPhrase()
    {
        $localizedException = new UrlAlreadyExistsException();

        $this->assertEquals(
            'rendered message',
            $localizedException->getMessage()
        );
    }
}
