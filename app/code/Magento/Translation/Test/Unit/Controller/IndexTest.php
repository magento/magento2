<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Controller;

use Exception;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Translation\Controller\Ajax\Index as AjaxIndex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    private const STUB_EMPTY_TRANSLATION_ARRAY = [];
    private const STUB_ERROR = 'Error';
    private const STUB_TRANSLATION_ARRAY = ['some', 'translations'];
    private const STUB_TRANSLATED_ARRAY = ['translated', 'strings'];

    /** @var MockObject|RequestInterface */
    private $requestMock;

    /** @var MockObject|JsonFactory */
    private $jsonResultFactoryMock;

    /** @var MockObject|Json */
    private $jsonResultMock;

    /** @var MockObject|ParserInterface */
    private $inlineParserMock;

    /** @var MockObject|ActionFlag */
    private $acitonFlagMock;

    /** @var AjaxIndex */
    private $indexAction;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();

        $this->jsonResultFactoryMock = $this->createPartialMock(JsonFactory::class, ['create']);
        $this->jsonResultMock = $this->createPartialMock(Json::class, ['setData']);
        $this->jsonResultFactoryMock->method('create')->willReturn($this->jsonResultMock);

        $this->inlineParserMock = $this->getMockBuilder(ParserInterface::class)
            ->setMethods(['processAjaxPost'])
            ->getMockForAbstractClass();

        $this->acitonFlagMock = $this->createPartialMock(ActionFlag::class, ['set']);

        $this->indexAction = new AjaxIndex(
            $this->requestMock,
            $this->jsonResultFactoryMock,
            $this->inlineParserMock,
            $this->acitonFlagMock
        );
    }

    public function testReturnsErrorWhenRequestFailed()
    {
        // Given
        $this->requestMock->method('getPost')
            ->willReturn(self::STUB_EMPTY_TRANSLATION_ARRAY);
        $this->inlineParserMock->method('processAjaxPost')
            ->willThrowException(new Exception(self::STUB_ERROR));

        // Expects
        $this->jsonResultMock->expects($this->once())
            ->method('setData')
            ->with(['error' => true, 'message' => self::STUB_ERROR]);
        $this->acitonFlagMock->expects($this->once())
            ->method('set');

        // When
        $this->indexAction->execute();
    }

    public function testReturnsInlineTranslationResult()
    {
        // Given
        $this->requestMock->method('getPost')
            ->willReturn(self::STUB_TRANSLATION_ARRAY);
        $this->inlineParserMock->method('processAjaxPost')
            ->willReturn(self::STUB_TRANSLATED_ARRAY);

        // Expects
        $this->jsonResultMock->expects($this->once())
            ->method('setData')
            ->with(self::STUB_TRANSLATED_ARRAY);
        $this->acitonFlagMock->expects($this->once())
            ->method('set');

        // When
        $this->indexAction->execute();
    }
}
