<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * Class NoSuchEntityExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class NoSuchEntityExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Phrase\RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    /**
     * @var \Magento\Framework\Phrase\Renderer\Placeholder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererMock;

    public function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder('Magento\Framework\Phrase\Renderer\Placeholder')
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
    }

    public function testConstructor()
    {
        $this->renderedMessage = 'rendered message';
        $this->rendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($this->renderedMessage));
        \Magento\Framework\Phrase::setRenderer($this->rendererMock);
        $message = 'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $expectedLogMessage = 'message parameter1 parameter2';
        $cause = new \Exception();
        $localizeException = new NoSuchEntityException(
            $message,
            $params,
            $cause
        );

        $this->assertEquals(0, $localizeException->getCode());

        $this->assertEquals($message, $localizeException->getRawMessage());
        $this->assertEquals($this->renderedMessage, $localizeException->getMessage());
        $this->assertEquals($expectedLogMessage, $localizeException->getLogMessage());

        $this->assertSame($cause, $localizeException->getPrevious());
    }

    /**
     * @param $message
     * @param $expectedMessage
     * @dataProvider constantsDataProvider
     */
    public function testConstants($message, $expectedMessage)
    {
        $this->renderedMessage = $message;
        $this->rendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($this->renderedMessage));
        \Magento\Framework\Phrase::setRenderer($this->rendererMock);

        $exception = new NoSuchEntityException(
            $message,
            ['consumer_id' => 1, 'resources' => 'record2']
        );
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function constantsDataProvider()
    {
        return [
            'singleFields' => [
                NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                'No such entity with %fieldName = %fieldValue',
            ],
            'doubleFields' => [
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
            ]
        ];
    }

    public function testSingleField()
    {
        $fieldName = 'storeId';
        $fieldValue = 15;
        $this->assertSame(
            "No such entity with $fieldName = $fieldValue",
            NoSuchEntityException::singleField($fieldName, $fieldValue)->getMessage()
        );
    }

    public function testDoubleField()
    {
        $website = 'website';
        $websiteValue = 15;
        $email = 'email';
        $emailValue = 'example@magento.com';
        NoSuchEntityException::doubleField($website, $websiteValue, $email, $emailValue);
        $this->assertSame(
            "No such entity with $website = $websiteValue, $email = $emailValue",
            NoSuchEntityException::doubleField($website, $websiteValue, $email, $emailValue)->getMessage()
        );
    }
}
