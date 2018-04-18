<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test Forward
 *
 * getRequest,getResponse of AbstractAction class is also tested
 */
class ForwardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Forward
     */
    protected $actionAbstract;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $cookieMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        )->disableOriginalConstructor()->getMock();
        $cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Http\Context')->disableOriginalConstructor()
            ->getMock();
        $this->response = $objectManager->getObject(
            'Magento\Framework\App\Response\Http',
            [
                'cookieManager' => $cookieManagerMock,
                'cookieMetadataFactory' => $cookieMetadataFactoryMock,
                'context' => $contextMock
            ]
        );

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();

        $this->actionAbstract = $objectManager->getObject(
            'Magento\Framework\App\Action\Forward',
            [
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    public function testDispatch()
    {
        $this->request->expects($this->once())->method('setDispatched')->with(false);
        $this->actionAbstract->dispatch($this->request);
    }

    /**
     * Test for getRequest method
     *
     * @test
     * @covers \Magento\Framework\App\Action\AbstractAction::getRequest
     */
    public function testGetRequest()
    {
        $this->assertSame($this->request, $this->actionAbstract->getRequest());
    }

    /**
     * Test for getResponse method
     *
     * @test
     * @covers \Magento\Framework\App\Action\AbstractAction::getResponse
     */
    public function testGetResponse()
    {
        $this->assertSame($this->response, $this->actionAbstract->getResponse());
    }

    /**
     * Test for getResponse med. Checks that response headers are set correctly
     *
     * @test
     * @covers \Magento\Framework\App\Action\AbstractAction::getResponse
     */
    public function testResponseHeaders()
    {
        $this->assertEmpty($this->actionAbstract->getResponse()->getHeaders());
    }
}
