<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit\Transfer\Adapter;

use Laminas\Http\Headers;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\File\Mime;
use Magento\Framework\File\Transfer\Adapter\Http;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\PhptTestCase;

/**
 * Tests http transfer adapter.
 */
class HttpTest extends TestCase
{
    /**
     * @var RequestHttp|MockObject
     */
    private $request;

    /**
     * @var Response|MockObject
     */
    private $response;

    /**
     * @var Http|MockObject
     */
    private $object;

    /**
     * @var Mime|MockObject
     */
    private $mime;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objects = [
            [
                HttpRequest::class,
                $this->createMock(HttpRequest::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);
        $this->response = $this->createPartialMock(
            Response::class,
            ['setHeader', 'sendHeaders', 'setHeaders']
        );
        $this->mime = $this->createMock(Mime::class);
        $this->request = $this->createPartialMock(
            RequestHttp::class,
            ['isHead']
        );
        $this->object = new Http($this->response, $this->mime, $this->request);
    }

    /**
     * @return void
     */
    public function testSend(): void
    {
        $file = __DIR__ . '/../../_files/javascript.js';
        $contentType = 'content/type';

        $this->response
            ->method('setHeader')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($file, $contentType) {
                    if ($arg1 == 'Content-length' && $arg2 == filesize($file)) {
                        return null;
                    } elseif ($arg1 == 'Content-Type' && $arg2 == $contentType) {
                        return null;
                    }
                }
            );
        $this->response->expects($this->once())
            ->method('sendHeaders');
        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($file)
            ->willReturn($contentType);
        $this->request->expects($this->once())
            ->method('isHead')
            ->willReturn(false);
        $this->expectOutputString(file_get_contents($file));

        $this->object->send($file);
    }

    /**
     * @return void
     */
    public function testSendWithOptions(): void
    {
        $file = __DIR__ . '/../../_files/javascript.js';
        $contentType = 'content/type';

        $headers = $this->getMockBuilder(Headers::class)
            ->getMock();
        $this->response->expects($this->atLeastOnce())
            ->method('setHeader')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($file, $contentType) {
                    if ($arg1 == 'Content-length' && $arg2 == filesize($file)) {
                        return null;
                    } elseif ($arg1 == 'Content-Type' && $arg2 == $contentType) {
                        return null;
                    }
                }
            );
        $this->response->expects($this->atLeastOnce())
            ->method('setHeaders')
            ->with($headers);
        $this->response->expects($this->once())
            ->method('sendHeaders');
        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($file)
            ->willReturn($contentType);
        $this->request->expects($this->once())
            ->method('isHead')
            ->willReturn(false);
        $this->expectOutputString(file_get_contents($file));

        $this->object->send(['filepath' => $file, 'headers' => $headers]);
    }
    /**
     * @return void
     */
    public function testSendNoFileSpecifiedException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Filename is not set');
        $this->object->send([]);
    }

    /**
     * @return void
     */
    public function testSendNoFileExistException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('File \'nonexistent.file\' does not exists');
        $this->object->send('nonexistent.file');
    }

    /**
     * @return void
     */
    public function testSendHeadRequest(): void
    {
        $file = __DIR__ . '/../../_files/javascript.js';
        $contentType = 'content/type';

        $this->response
            ->method('setHeader')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($file, $contentType) {
                    if ($arg1 == 'Content-length' && $arg2 == filesize($file)) {
                        return null;
                    } elseif ($arg1 == 'Content-Type' && $arg2 == $contentType) {
                        return null;
                    }
                }
            );
        $this->response->expects($this->once())
            ->method('sendHeaders');
        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($file)
            ->willReturn($contentType);
        $this->request->expects($this->once())
            ->method('isHead')
            ->willReturn(true);

        $this->object->send($file);
        $phpTest = new PhptTestCase($file);
        $this->assertFalse($phpTest->hasOutput());
    }
}
