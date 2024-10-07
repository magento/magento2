<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\MediaStorage\Model\File\Storage\Response as FileResponse;
use Magento\PageCache\Model\App\Response\HttpPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\PageCache\Model\App\Response\HttpPlugin.
 */
class HttpPluginTest extends TestCase
{
    /**
     * @var HttpPlugin
     */
    private $httpPlugin;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var HttpRequest|MockObject
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(HttpRequest::class);
        $this->httpPlugin = new HttpPlugin(
            $this->context,
            $this->request
        );
    }

    /**
     * @param string $responseClass
     * @param bool $headersSent
     * @param int $sendVaryCalled
     * @return void
     *
     * @dataProvider beforeSendResponseDataProvider
     */
    public function testBeforeSendResponse(string $responseClass, bool $headersSent, int $sendVaryCalled): void
    {
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock($responseClass);
        $responseMock->expects($this->any())->method('headersSent')->willReturn($headersSent);
        $responseMock->expects($this->exactly($sendVaryCalled))->method('sendVary');

        $this->httpPlugin->beforeSendResponse($responseMock);
    }

    /**
     * @return array
     */
    public static function beforeSendResponseDataProvider(): array
    {
        return [
            'http_response_headers_not_sent' => [HttpResponse::class, false, 1],
            'http_response_headers_sent' => [HttpResponse::class, true, 0],
            'file_response_headers_not_sent' => [FileResponse::class, false, 0],
            'file_response_headers_sent' => [FileResponse::class, true, 0],
        ];
    }

    public function testBeforeSendResponseVaryMismatch()
    {
        /** @var HttpResponse|MockObject $responseMock */
        $this->context->expects($this->any())->method('getVaryString')->willReturn('currentVary');
        $this->request->expects($this->any())->method('get')->willReturn('varyCookie');
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->once())->method('setNoCacheHeaders');
        $responseMock->expects($this->once())->method('sendVary');

        $this->httpPlugin->beforeSendResponse($responseMock);
    }
}
