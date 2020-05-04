<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Test\Unit\TemplateEngine\Xhtml;

use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Ui\Component\Listing;
use Magento\Ui\TemplateEngine\Xhtml\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Ui\TemplateEngine\Xhtml\Result.
 */
class ResultTest extends TestCase
{
    /**
     * Stub simple html element
     */
    private const STUB_HTML_ELEMENT = '<div id="id"></div>';

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
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->templateMock = $this->createMock(Template::class);
        $this->compilerMock = $this->getMockForAbstractClass(CompilerInterface::class);
        $this->componentMock = $this->createMock(Listing::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->stateMock = $this->createMock(State::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Result::class,
            [
                'template' => $this->templateMock,
                'compiler' => $this->compilerMock,
                'component' => $this->componentMock,
                'logger' => $this->loggerMock,
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
        $e = new \Exception();

        $this->templateMock->expects($this->once())
            ->method('getDocumentElement')
            ->willThrowException($e);
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($e);
        $this->assertEquals(
            '<pre><code>Exception in ' . $e->getFile() . ':' . $e->getLine() . '</code></pre>',
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
            ->setConstructorArgs(['arg'])
            ->getMock();

        $this->templateMock->expects($this->once())
            ->method('getDocumentElement')
            ->willReturn($domElementMock);
        $this->compilerMock->expects($this->once())
            ->method('compile')
            ->with(
                $this->isInstanceOf(\DOMElement::class),
                $this->componentMock,
                $this->componentMock
            );
        $this->templateMock->expects($this->once())->method('__toString');
        $this->compilerMock->expects($this->once())
            ->method('postprocessing')
            ->willReturn(self::STUB_HTML_ELEMENT);

        $this->assertEquals(self::STUB_HTML_ELEMENT, $this->model->__toString());
    }
}
