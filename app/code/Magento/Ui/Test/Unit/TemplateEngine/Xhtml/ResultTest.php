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
        $this->templateMock = $this->getMockBuilder(Template::class)
            ->setMethods(['getDocumentElement'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->compilerMock = $this->getMockBuilder(CompilerInterface::class)
            ->setMethods(['compile'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->componentMock = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializerMock = $this->getMockBuilder(JsonHexTag::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $this->templateMock->method('getDocumentElement')->willThrowException($exception);
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
            ->enableOriginalConstructor()
            ->setConstructorArgs(['new'])
            ->getMock();
        $this->templateMock->method('getDocumentElement')->willReturn($domElementMock);
        $this->compilerMock->method('compile')
            ->with($domElementMock, $this->componentMock, $this->componentMock)->willReturn(true);

        $this->assertInternalType('string', $this->model->__toString());
    }
}
