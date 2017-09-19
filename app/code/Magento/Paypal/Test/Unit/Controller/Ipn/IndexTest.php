<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Controller\Ipn;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /** @var Index */
    protected $model;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Paypal\Controller\Ipn\Index::class,
            [
                'logger' => $this->logger,
                'request' => $this->request,
                'response' => $this->response,
            ]
        );
    }

    public function testIndexActionException()
    {
        $this->request->expects($this->once())->method('isPost')->will($this->returnValue(true));
        $exception = new \Exception();
        $this->request->expects($this->once())->method('getPostValue')->will($this->throwException($exception));
        $this->logger->expects($this->once())->method('critical')->with($this->identicalTo($exception));
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->model->execute();
    }
}
