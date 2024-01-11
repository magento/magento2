<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Helper\Rss;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RssTest extends TestCase
{
    /**
     * @var Rss
     */
    protected $model;

    /**
     * @var WishlistFactory|MockObject
     */
    protected $wishlistFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var DecoderInterface|MockObject
     */
    protected $urlDecoderMock;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var Manager|MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->wishlistFactoryMock = $this->getMockBuilder(WishlistFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $this->urlDecoderMock = $this->getMockBuilder(DecoderInterface::class)
            ->getMock();

        $this->customerFactoryMock = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            Rss::class,
            [
                'wishlistFactory' => $this->wishlistFactoryMock,
                'httpRequest' => $this->requestMock,
                'urlDecoder' => $this->urlDecoderMock,
                'customerFactory' => $this->customerFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'moduleManager' => $this->moduleManagerMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetWishlistWithWishlistId(): void
    {
        $wishlistId = 1;

        $wishlist = $this->getMockBuilder(Wishlist::class)
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

    /**
     * @return void
     */
    public function testGetWishlistWithCustomerId(): void
    {
        $customerId = 1;
        $data = $customerId . ',2';

        $wishlist = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->urlDecoderMock->expects($this->any())
            ->method('decode')
            ->willReturnArgument(0);

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($data) {
                if ($arg1 == 'wishlist_id' && empty($arg2)) {
                    return '';
                } elseif ($arg1 == 'data' && empty($arg2)) {
                    return $data;
                }
            });

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(0);

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

    /**
     * @return void
     */
    public function testGetCustomerWithSession(): void
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

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
     *
     * @return void
     * @dataProvider dataProviderIsRssAllow
     */
    public function testIsRssAllow(
        bool $isModuleEnabled,
        bool $isWishlistActive,
        bool $result
    ): void {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_Rss')
            ->willReturn($isModuleEnabled);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with('rss/wishlist/active', ScopeInterface::SCOPE_STORE)
            ->willReturn($isWishlistActive);

        $this->assertEquals($result, $this->model->isRssAllow());
    }

    /**
     * @return array
     */
    public static function dataProviderIsRssAllow(): array
    {
        return [
            [false, false, false],
            [true, false, false],
            [false, true, false],
            [true, true, true]
        ];
    }
}
