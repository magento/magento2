<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;
use Magento\Framework\Phrase\RendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoSuchEntityExceptionTest extends TestCase
{
    /** @var RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    /**
     * @var Placeholder|MockObject
     */
    protected $rendererMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->defaultRenderer = Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder(Placeholder::class)
            ->onlyMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->renderedMessage = 'rendered message';
        $this->rendererMock->expects($this->once())
            ->method('render')
            ->willReturn($this->renderedMessage);
        Phrase::setRenderer($this->rendererMock);
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
