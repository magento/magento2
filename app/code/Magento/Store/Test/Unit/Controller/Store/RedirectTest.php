<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreResolver;

/**
 * Test class for \Magento\Store\Controller\Store\SwitchAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Controller\Store\SwitchAction
     */
    private $model;

    /**
     * @var StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var StoreResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeResolverMock;


    /**
     * @return void
     */
    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->getMock();
        $this->storeRepositoryMock = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHttpHost'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->storeResolverMock = $this->getMockBuilder(StoreResolverInterface::class)->getMock();
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Store\Controller\Store\Redirect::class,
            [
                'storeRepository' => $this->storeRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                'storeResolver' => $this->storeResolverMock,
                '_request' => $this->requestMock,
                '_response' => $this->responseMock,
                '_redirect' => $this->redirectMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $storeToSwitchToCode = 'sv2';
        $defaultStoreViewCode = 'default';
        $defaultStoreViewMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();
        $storeToSwitchToMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUseStoreInUrl'])
            ->getMockForAbstractClass();

        $this->storeResolverMock
            ->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturn(1);

        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($defaultStoreViewCode);
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                [StoreResolver::PARAM_NAME, null, $storeToSwitchToCode],
                ['___from_store', null, $defaultStoreViewCode]
            ]
        );
        $this->storeRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [$defaultStoreViewCode, $defaultStoreViewMock],
                    [$storeToSwitchToCode, $storeToSwitchToMock]
                ]
            );

        $defaultStoreViewMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn("default");

        $this->storeManagerMock
            ->expects($this->once())
            ->method('setCurrentStore')
            ->with($storeToSwitchToMock);

        $this->redirectMock->expects($this->once())->method('redirect');

        $this->model->execute();
    }
}
