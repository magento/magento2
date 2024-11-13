<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Product\Frontend\Action;

use Laminas\Http\AbstractMessage;
use Laminas\Http\Response;
use Magento\Catalog\Controller\Product\Frontend\Action\Synchronize;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynchronizeTest extends TestCase
{
    /**
     * @var Synchronize
     */
    private $synchronize;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Synchronizer|MockObject
     */
    private $synchronizerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $jsonFactoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizerMock = $this->getMockBuilder(Synchronizer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->synchronize = new Synchronize(
            $this->contextMock,
            $this->synchronizerMock,
            $this->jsonFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecuteAction(): void
    {
        $data = [
            'type_id' => null,
            'ids' => []
        ];

        $jsonObject = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonObject);

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($data) {
                if ($arg1 == 'ids' && empty($arg2)) {
                    return $data['ids'];
                } elseif ($arg1 == 'type_id' && $arg2 === null) {
                    return $data['type_id'];
                }
            });

        $this->synchronizerMock->expects($this->once())
            ->method('syncActions')
            ->with([], null);

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([]);

        $this->synchronize->execute();
    }

    /**
     * @return void
     */
    public function testExecuteActionException(): void
    {
        $data = [
            'type_id' => null,
            'ids' => []
        ];
        $jsonObject = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonObject);

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($data) {
                if ($arg1 == 'ids' && empty($arg2)) {
                    return $data['ids'];
                } elseif ($arg1 == 'type_id' && $arg2 === null) {
                    return $data['type_id'];
                }
            });

        $this->synchronizerMock->expects($this->once())
            ->method('syncActions')
            ->willThrowException(new \Exception());

        $jsonObject->expects($this->once())
            ->method('setStatusHeader')
            ->with(
                Response::STATUS_CODE_400,
                AbstractMessage::VERSION_11,
                'Bad Request'
            );
        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([]);

        $this->synchronize->execute();
    }
}
