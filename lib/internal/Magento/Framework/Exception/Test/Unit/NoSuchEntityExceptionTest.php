<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * Class NoSuchEntityExceptionTest
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

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder('Magento\Framework\Phrase\Renderer\Placeholder')
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * @return void
     */
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
     * @return void
     */
    public function testSingleField()
    {
        $fieldName = 'storeId';
        $fieldValue = 15;
        $this->assertSame(
            "No such entity with $fieldName = $fieldValue",
            NoSuchEntityException::singleField($fieldName, $fieldValue)->getMessage()
        );
    }

    /**
     * @return void
     */
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
