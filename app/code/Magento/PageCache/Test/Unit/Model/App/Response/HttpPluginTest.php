<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Response;

use Magento\Framework\App\Response\Http as HttpResponse;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->httpPlugin = new HttpPlugin();
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
    public function beforeSendResponseDataProvider(): array
    {
        return [
            'http_response_headers_not_sent' => [HttpResponse::class, false, 1],
            'http_response_headers_sent' => [HttpResponse::class, true, 0],
            'file_response_headers_not_sent' => [FileResponse::class, false, 0],
            'file_response_headers_sent' => [FileResponse::class, true, 0],
        ];
    }
}
