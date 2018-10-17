<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/** Unit tests for \Magento\MediaStorage\Model\File\Storage\Response class */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Response
     */
    private $response;

    /**
     * @var \Magento\Framework\File\Transfer\Adapter\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transferAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->transferAdapter = $this->getMockBuilder(\Magento\Framework\File\Transfer\Adapter\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();
        $this->response = $objectManager->getObject(
            \Magento\MediaStorage\Model\File\Storage\Response::class,
            [
                'transferAdapter' => $this->transferAdapter,
                'statusCode' => 200,
            ]
        );
    }

    public function testSendResponse()
    {
        $filePath = 'file_path';
        $headers = $this->getMockBuilder(\Zend\Http\Headers::class)->getMock();
        $this->response->setFilePath($filePath);
        $this->response->setHeaders($headers);
        $this->transferAdapter->expects($this->atLeastOnce())->method('send')
            ->with(['filepath' => $filePath, 'headers' => $headers]);
        $this->response->sendResponse();
    }

    public function testSendResponseWithoutFilePath()
    {
        $this->transferAdapter->expects($this->never())->method('send');
        $this->response->sendResponse();
    }
}
