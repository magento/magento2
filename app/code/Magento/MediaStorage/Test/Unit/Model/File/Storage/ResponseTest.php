<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Laminas\Http\Headers;
use Magento\Framework\File\Transfer\Adapter\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Storage\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** Unit tests for \Magento\MediaStorage\Model\File\Storage\Response class */
class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var Http|MockObject
     */
    private $transferAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->transferAdapter = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();
        $this->response = $objectManager->getObject(
            Response::class,
            [
                'transferAdapter' => $this->transferAdapter,
                'statusCode' => 200,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSendResponse(): void
    {
        $filePath = 'file_path';
        $headers = $this->getMockBuilder(Headers::class)
            ->getMock();
        $this->response->setFilePath($filePath);
        $this->response->setHeaders($headers);
        $this->transferAdapter
            ->expects($this->atLeastOnce())
            ->method('send')
            ->with(
                [
                    'filepath' => $filePath,
                    'headers' => $headers,
                ]
            );

        $this->response->sendResponse();
    }

    /**
     * @return void
     */
    public function testSendResponseWithoutFilePath(): void
    {
        $this->transferAdapter->expects($this->never())->method('send');
        $this->response->sendResponse();
    }
}
