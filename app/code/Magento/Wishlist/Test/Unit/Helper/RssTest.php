<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Helper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RssTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Wishlist\Helper\Rss
     */
    protected $model;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlDecoderMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\Module\ModuleManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->wishlistFactoryMock = $this->getMockBuilder(\Magento\Wishlist\Model\WishlistFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();

        $this->urlDecoderMock = $this->getMockBuilder(\Magento\Framework\Url\DecoderInterface::class)
            ->getMock();

        $this->customerFactoryMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\ModuleManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            \Magento\Wishlist\Helper\Rss::class,
            [
                'wishlistFactory' => $this->wishlistFactoryMock,
                'httpRequest' => $this->requestMock,
                'urlDecoder' => $this->urlDecoderMock,
                'customerFactory' => $this->customerFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'moduleManager' => $this->moduleManagerMock,
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    public function testGetWishlistWithWishlistId()
    {
        $wishlistId = 1;

        $wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('wishlist_id', null)
            ->willReturn($wishlistId);

        $wishlist->expects($this->once())
            ->method('load')
            ->with($wishlistId, null)
            ->willReturnSelf();

        $this->assertEquals($wishlist, $this->model->getWishlist());
        // Check that wishlist is cached
        $this->assertSame($wishlist, $this->model->getWishlist());
    }

    public function testGetWishlistWithCustomerId()
    {
        $customerId = 1;
        $data = $customerId . ',2';

        $wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('wishlist_id', null)
            ->willReturn('');

        $this->urlDecoderMock->expects($this->any())
            ->method('decode')
            ->willReturnArgument(0);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('data', null)
            ->willReturn($data);

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(0);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customer);

        $this->customerRepositoryMock->expects($this->never())
            ->method('getById');

        $customer->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($customerId);

        $wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->with($customerId, false)
            ->willReturnSelf();

        $this->assertEquals($wishlist, $this->model->getWishlist());
    }

    public function testGetCustomerWithSession()
    {
        $customerId = 1;
        $data = $customerId . ',2';

        $this->urlDecoderMock->expects($this->any())
            ->method('decode')
            ->willReturnArgument(0);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('data', null)
            ->willReturn($data);

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->customerFactoryMock->expects($this->never())
            ->method('create');

        $this->assertEquals($customer, $this->model->getCustomer());
        // Check that customer is cached
        $this->assertSame($customer, $this->model->getCustomer());
    }

    /**
     * @param bool $isModuleEnabled
     * @param bool $isWishlistActive
     * @param bool $result
     * @dataProvider dataProviderIsRssAllow
     */
    public function testIsRssAllow($isModuleEnabled, $isWishlistActive, $result)
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_Rss')
            ->willReturn($isModuleEnabled);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with('rss/wishlist/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($isWishlistActive);

        $this->assertEquals($result, $this->model->isRssAllow());
    }

    /**
     * @return array
     */
    public function dataProviderIsRssAllow()
    {
        return [
            [false, false, false],
            [true, false, false],
            [false, true, false],
            [true, true, true],
        ];
    }
}
