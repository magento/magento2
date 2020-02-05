<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\TemplateEngine\Xhtml;

use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Ui\TemplateEngine\Xhtml\Result;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObject;

/**
 * Test for \Magento\Ui\TemplateEngine\Xhtml\Result.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResultTest extends TestCase
{
    /**
     * @var Result
     */
    private $model;

    /**
     * @var ObjectManagerHelper|MockObject
     */
    private $objectManagerHelper;

    /**
     * @var Template|MockObject
     */
    private $templateMock;

    /**
     * @var CompilerInterface|MockObject
     */
    private $compilerMock;

    /**
     * @var UiComponentInterface|MockObject
     */
    private $componentMock;

    /**
     * @var Structure|MockObject
     */
    private $structureMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var JsonHexTag|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->templateMock = $this->createMock(Template::class);
        $this->compilerMock = $this->createMock(CompilerInterface::class);
        $this->componentMock = $this->createMock(\Magento\Ui\Component\Listing::class);
        $this->structureMock = $this->createMock(Structure::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->stateMock = $this->createMock(State::class);
        $this->jsonSerializerMock = $this->createMock(JsonHexTag::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Result::class,
            [
                'template' => $this->templateMock,
                'compiler' => $this->compilerMock,
                'component' => $this->componentMock,
                'structure' => $this->structureMock,
                'logger' => $this->loggerMock,
                'jsonSerializer' => $this->jsonSerializerMock,
                'state' => $this->stateMock,
            ]
        );
    }

    /**
     * To string method with exception message
     *
     * @return void
     */
    public function testToStringWithException(): void
    {
        $exception = new \Exception();

        $this->templateMock->expects($this->once())
           ->method('getDocumentElement')
           ->willThrowException($exception);
        $this->stateMock->method('getMode')->willReturn(State::MODE_DEVELOPER);

        $this->assertEquals(
            '<pre><code>' . $exception->__toString() . '</code></pre>',
            $this->model->__toString()
        );
    }

    /**
     * To string method
     *
     * @return void
     */
    public function testToString(): void
    {
        $domElementMock = $this->getMockBuilder(\DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();

        $this->templateMock->expects($this->exactly(2))
            ->method('getDocumentElement')
            ->willReturn($domElementMock);
        $this->compilerMock->expects($this->once())
            ->method('compile')
            ->with(
                $this->isInstanceOf('\DOMElement'), 
                $this->componentMock, 
                $this->componentMock
            );

        $this->assertEquals('string', $this->model->__toString());
    }
}
