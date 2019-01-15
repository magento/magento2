<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Product\Frontend\Action;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Controller\Product\Frontend\Action\Synchronize;

class SynchronizeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Controller\Product\Frontend\Action\Synchronize
     */
    private $synchronize;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Synchronizer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $synchronizerMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonFactoryMock;

    protected function setUp()
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

    public function testExecuteAction()
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

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('ids', [])
            ->willReturn($data['ids']);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('type_id', null)
            ->willReturn($data['type_id']);

        $this->synchronizerMock->expects($this->once())
            ->method('syncActions')
            ->with([], null);

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([]);

        $this->synchronize->execute();
    }
    
    public function testExecuteActionException()
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

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('ids', [])
            ->willReturn($data['ids']);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('type_id', null)
            ->willReturn($data['type_id']);

        $this->synchronizerMock->expects($this->once())
            ->method('syncActions')
            ->willThrowException(new \Exception);

        $jsonObject->expects($this->once())
            ->method('setStatusHeader')
            ->with(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([]);

        $this->synchronize->execute();
    }
}
