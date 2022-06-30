<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Guest;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestTest extends TestCase
{
    /**
     * @var Guest
     */
    protected $guest;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var CookieManagerInterface|MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $managerInterfaceMock;

    /**
     * @var MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewInterfaceMock;

    /**
     * @var Store|MockObject
     */
    protected $storeModelMock;

    /**
     * @var Order|MockObject
     */
    protected $salesOrderMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $appContextHelperMock = $this->createMock(Context::class);
        $storeManagerInterfaceMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $registryMock = $this->createMock(Registry::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(
            CookieMetadataFactory::class
        );
        $this->managerInterfaceMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create']);
        $this->viewInterfaceMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->storeModelMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesOrderMock = $this->createPartialMock(
            Order::class,
            [
                'getProtectCode',
                'loadByIncrementIdAndStoreId',
                'loadByIncrementId',
                'getId',
                'getStoreId',
                'getBillingAddress'
            ]
        );
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->onlyMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->onlyMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderSearchResult = $this->getMockBuilder(OrderSearchResultInterface::class)
            ->onlyMethods(['getTotalCount', 'getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultRedirectFactory =
            $this->getMockBuilder(RedirectFactory::class)
                ->onlyMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->orderRepository->method('getList')->willReturn($orderSearchResult);
        $orderSearchResult->method('getTotalCount')->willReturn(1);
        $orderSearchResult->method('getItems')->willReturn([ 2 => $this->salesOrderMock]);
        $searchCriteria = $this
            ->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $storeManagerInterfaceMock->expects($this->any())->method('getStore')->willReturn($this->storeModelMock);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $this->storeModelMock->method('getId')->willReturn(1);
        $this->salesOrderMock->expects($this->any())->method('getStoreId')->willReturn(1);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->guest = $objectManagerHelper->getObject(
            Guest::class,
            [
                'context' => $appContextHelperMock,
                'storeManager' => $storeManagerInterfaceMock,
                'coreRegistry' => $registryMock,
                'customerSession' => $this->sessionMock,
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'messageManager' => $this->managerInterfaceMock,
                'orderFactory' => $this->orderFactoryMock,
                'view' => $this->viewInterfaceMock,
                'orderRepository' => $this->orderRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'resultRedirectFactory' => $resultRedirectFactory
            ]
        );
    }

    /**
     * Test load valid order with non empty post data.
     *
     * @param array $post
     *
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @dataProvider loadValidOrderNotEmptyPostDataProvider
     */
    public function testLoadValidOrderNotEmptyPost(array $post): void
    {
        $incrementId = $post['oar_order_id'];
        $protectedCode = 'protectedCode';
        $this->sessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->once())->method('getPostValue')->willReturn($post);

        $this->searchCriteriaBuilder
            ->method('addFilter')
            ->withConsecutive(
                ['increment_id', trim($incrementId)],
                ['store_id', $this->storeModelMock->getId()]
            )->willReturnOnConsecutiveCalls($this->searchCriteriaBuilder, $this->searchCriteriaBuilder);

        $this->salesOrderMock->expects($this->any())->method('getId')->willReturn($incrementId);

        $billingAddressMock = $this->createPartialMock(
            Address::class,
            ['getLastname', 'getEmail', 'getPostcode']
        );
        $billingAddressMock->expects($this->once())->method('getLastname')
            ->willReturn($post['oar_billing_lastname']);
        $billingAddressMock->expects($this->any())->method('getEmail')->willReturn($post['oar_email']);
        $billingAddressMock->expects($this->any())->method('getPostcode')->willReturn($post['oar_zip']);
        $this->salesOrderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->salesOrderMock->expects($this->once())->method('getProtectCode')->willReturn($protectedCode);
        $metaDataMock = $this->createMock(PublicCookieMetadata::class);
        $metaDataMock->expects($this->once())->method('setPath')
            ->with(Guest::COOKIE_PATH)
            ->willReturnSelf();
        $metaDataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->willReturnSelf();
        $metaDataMock->expects($this->once())
            ->method('setSameSite')
            ->with('Lax')
            ->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($metaDataMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(Guest::COOKIE_NAME, $this->anything(), $metaDataMock);
        $this->assertTrue($this->guest->loadValidOrder($requestMock));
    }

    /**
     * Load valid order with non empty post data provider.
     *
     * @return array
     */
    public function loadValidOrderNotEmptyPostDataProvider(): array
    {
        return [
            [
                [
                    'oar_order_id' => '1',
                    'oar_type' => 'email',
                    'oar_billing_lastname' => 'White',
                    'oar_email' => 'test@magento-test.com',
                    'oar_zip' => ''
                ]
            ],
            [
                [
                    'oar_order_id' => ' 14  ',
                    'oar_type' => 'email',
                    'oar_billing_lastname' => 'Black  ',
                    'oar_email' => '        test1@magento-test.com  ',
                    'oar_zip' => ''
                ]
            ],
            [
                [
                    'oar_order_id' => ' 14  ',
                    'oar_type' => 'zip',
                    'oar_billing_lastname' => 'Black  ',
                    'oar_email' => '        test1@magento-test.com  ',
                    'oar_zip' => '123456  '
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    public function testLoadValidOrderStoredCookie(): void
    {
        $protectedCode = 'protectedCode';
        $incrementId = '1';
        $cookieData = $protectedCode . ':' . $incrementId;
        $cookieDataHash = base64_encode($cookieData);
        $this->sessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Guest::COOKIE_NAME)
            ->willReturn($cookieDataHash);

        $this->searchCriteriaBuilder
            ->method('addFilter')
            ->withConsecutive(
                ['increment_id', trim($incrementId)],
                ['store_id', $this->storeModelMock->getId()]
            )->willReturnOnConsecutiveCalls($this->searchCriteriaBuilder, $this->searchCriteriaBuilder);

        $this->salesOrderMock->expects($this->any())->method('getId')->willReturn($incrementId);
        $this->salesOrderMock->expects($this->once())->method('getProtectCode')->willReturn($protectedCode);
        $metaDataMock = $this->createMock(PublicCookieMetadata::class);
        $metaDataMock->expects($this->once())
            ->method('setPath')
            ->with(Guest::COOKIE_PATH)
            ->willReturnSelf();
        $metaDataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->willReturnSelf();
        $metaDataMock->expects($this->once())
            ->method('setSameSite')
            ->with('Lax')
            ->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($metaDataMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(Guest::COOKIE_NAME, $this->anything(), $metaDataMock);
        $requestMock = $this->createMock(Http::class);
        $this->assertTrue($this->guest->loadValidOrder($requestMock));
    }
}
