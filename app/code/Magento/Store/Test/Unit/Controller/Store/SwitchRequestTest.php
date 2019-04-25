<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;

/**
 * Test class for \Magento\Store\Controller\Store\SwitchRequest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitchRequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Controller\Store\SwitchRequest
     */
    private $model;

    /**
     * @var StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fromStoreMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepositoryMock =
            $this->getMockBuilder(CustomerRepositoryInterface::class)->getMock();
        $this->storeRepositoryMock =
            $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)
                ->disableOriginalConstructor()->setMethods(['get', 'getActiveStoreByCode'])->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()->getMock();
        $this->fromStoreMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Store\Controller\Store\SwitchRequest::class,
            [
                'customerSession' => $this->customerRepositoryMock,
                'deploymentConfig' => $this->deploymentConfigMock,
                'storeRepository' => $this->storeRepositoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                '_request' => $this->requestMock,
                '_response' => $this->responseMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $fromStoreCode = 'sv2';
        $targetStoreCode = 'default';
        $expectedRedirectUrl='/';
        $customerId=5;
        $timestamp='1556131830';

        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['___from_store', null, $fromStoreCode],
                ['customer_id', null, $customerId],
                ['time_stamp', null, $timestamp],
                ['___to_store', null, $targetStoreCode],
                ['signature', null, 'cbc099b3cc4a9a8f3a78a97e7a579ceff19a2b26a6c88b08f0f58442ea5bd968']
            ]);

        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($fromStoreCode)
            ->willReturn($this->fromStoreMock);

        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getActiveStoreByCode')
            ->with($targetStoreCode);

        $this->fromStoreMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
            ->willReturn($expectedRedirectUrl);

        $this->responseMock->expects($this->once())->method('setRedirect')->with($expectedRedirectUrl);
        $this->model->execute();
    }
}
