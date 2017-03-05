<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Helper\Backend;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorizenet\Helper\Backend\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->urlBuilderMock = $this->getMock(\Magento\Backend\Model\Url::class, ['getUrl'], [], '', false);

        $contextMock = $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false);
        $contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->orderFactoryMock = $this->getMock(\Magento\Sales\Model\OrderFactory::class, ['create'], [], '', false);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);

        $this->dataHelper = $helper->getObject(
            \Magento\Authorizenet\Helper\Backend\Data::class,
            [
                'context' => $contextMock,
                'storeManager' =>$this->storeManagerMock,
                'orderFactory' =>$this->orderFactoryMock,
                'backendUrl' =>$this->urlBuilderMock
            ]
        );
    }

    public function testGetPlaceOrderAdminUrl()
    {
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/authorizenet_directpost_payment/place')
            ->willReturn('some value');

        $this->assertEquals('some value', $this->dataHelper->getPlaceOrderAdminUrl());
    }

    public function testGetSuccessOrderUrl()
    {
        $orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            ['loadByIncrementId', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with('invoice number')
            ->willReturnSelf();

        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn('order id');

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sales/order/view', ['order_id' => 'order id'])
            ->willReturn('some value');

        $this->assertEquals(
            'some value',
            $this->dataHelper->getSuccessOrderUrl(['x_invoice_num' => 'invoice number', 'some param'])
        );
    }

    public function testGetRedirectIframeUrl()
    {
        $params = ['some params', '_secure' => true];
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/authorizenet_directpost_payment/redirect', $params)
            ->willReturn('some value');

        $this->assertEquals('some value', $this->dataHelper->getRedirectIframeUrl($params));
    }

    public function testGetRelayUrl()
    {
        $baseUrl = 'http://base.url/';

        $defaultStoreMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultStoreMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
            ->willReturn($baseUrl);

        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn(null);

        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$defaultStoreMock]);

        $this->assertSame(
            'http://base.url/authorizenet/directpost_payment/backendResponse',
            $this->dataHelper->getRelayUrl()
        );
    }
}
