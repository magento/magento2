<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Action;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Forward
 *
 * getRequest,getResponse of AbstractAction class is also tested
 */
class ForwardTest extends TestCase
{
    /**
     * @var Forward
     */
    protected $actionAbstract;

    /**
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $cookieMetadataFactoryMock = $this->getMockBuilder(
            CookieMetadataFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $objectManager->getObject(
            Http::class,
            [
                'cookieManager' => $cookieManagerMock,
                'cookieMetadataFactory' => $cookieMetadataFactoryMock,
                'context' => $contextMock
            ]
        );

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionAbstract = $objectManager->getObject(
            Forward::class,
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
