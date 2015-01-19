<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class ErrorMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The default Phrase renderer.
     *
     * @var \Magento\Framework\Phrase\RendererInterface
     */
    private $defaultRenderer;

    /**
     * The message that has been rendered by the current Phrase renderer.
     *
     * @var string
     */
    private $renderedMessage;

    /**
     * Initialization that runs before each new test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $rendererMock = $this->getMock('Magento\Framework\Phrase\Renderer\Placeholder', [], [], '', false);
        $this->renderedMessage = 'rendered message';
        $rendererMock
            ->expects($this->once())->method('render')->will($this->returnValue($this->renderedMessage));
        \Magento\Framework\Phrase::setRenderer($rendererMock);
    }

    /**
     * Restoration after each test runs. Reset the Phrase renderer back to the default one.
     *
     * @return void
     */
    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * Verify that the constructor works properly and check all associated error message types.
     *
     * @param string $message The error message
     * @param array $params The array of substitution parameters
     * @param string $expectedLogMessage The expected output of ErrorMessage::getLogMessage()
     *
     * @return void
     * @dataProvider errorMessageConstructorDataProvider
     */
    public function testConstructor($message, $params, $expectedLogMessage)
    {
        $errorMessage = new ErrorMessage($message, $params);

        $this->assertEquals($this->renderedMessage, $errorMessage->getMessage());
        $this->assertEquals($message, $errorMessage->getRawMessage());
        $this->assertEquals($expectedLogMessage, $errorMessage->getLogMessage());
        $this->assertEquals($params, $errorMessage->getParameters());
    }

    /**
     * Data provider for the constructor test.
     *
     * @return array
     */
    public function errorMessageConstructorDataProvider()
    {
        return [
            'withPositionalParameters' => [
                'message %1 %2',
                ['parameter1', 'parameter2'],
                'message parameter1 parameter2',
            ],
            'withNamedParameters' => [
                'message %key1 %key2',
                ['key1' => 'parameter1', 'key2' => 'parameter2'],
                'message parameter1 parameter2',
            ],
            'withNoParameters' => [
                'message',
                [],
                'message',
            ]
        ];
    }
}
