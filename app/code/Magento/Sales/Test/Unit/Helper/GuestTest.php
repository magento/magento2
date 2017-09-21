<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Helper\Guest;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Helper\Guest */
    protected $guest;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $appContextHelperMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerInterfaceMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieManagerMock;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieMetadataFactoryMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $orderFactoryMock;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewInterfaceMock;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeModelMock;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    protected $salesOrderMock;

    protected function setUp()
    {
        $this->appContextHelperMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->storeManagerInterfaceMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->sessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        );
        $this->managerInterfaceMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->orderFactoryMock = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $this->viewInterfaceMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->storeModelMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesOrderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [
                'getProtectCode', 'loadByIncrementIdAndStoreId', 'loadByIncrementId',
                'getId', 'getBillingAddress', '__wakeup'
            ]);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->guest = $this->objectManagerHelper->getObject(
            \Magento\Sales\Helper\Guest::class,
            [
                'context' => $this->appContextHelperMock,
                'storeManager' => $this->storeManagerInterfaceMock,
                'coreRegistry' => $this->registryMock,
                'customerSession' => $this->sessionMock,
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'messageManager' => $this->managerInterfaceMock,
                'orderFactory' => $this->orderFactoryMock,
                'view' => $this->viewInterfaceMock
            ]
        );
    }

    public function testLoadValidOrderNotEmptyPost()
    {
        $post = [
            'oar_order_id' => 1,
            'oar_type' => 'email',
            'oar_billing_lastname' => 'oar_billing_lastname',
            'oar_email' => 'oar_email',
            'oar_zip' => 'oar_zip',

        ];
        $storeId = '1';
        $incrementId = $post['oar_order_id'];
        $protectedCode = 'protectedCode';
        $this->sessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $requestMock->expects($this->once())->method('getPostValue')->willReturn($post);
        $this->storeManagerInterfaceMock->expects($this->once())->method('getStore')->willReturn($this->storeModelMock);
        $this->storeModelMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->orderFactoryMock->expects($this->once())->method('create')->willReturn($this->salesOrderMock);
        $this->salesOrderMock->expects($this->once())->method('loadByIncrementIdAndStoreId')->willReturnSelf();
        $this->salesOrderMock->expects($this->any())->method('getId')->willReturn($incrementId);

        $billingAddressMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getLastname', 'getEmail', '__wakeup']
        );
        $billingAddressMock->expects($this->once())->method('getLastname')->willReturn(($post['oar_billing_lastname']));
        $billingAddressMock->expects($this->once())->method('getEmail')->willReturn(($post['oar_email']));
        $this->salesOrderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->salesOrderMock->expects($this->once())->method('getProtectCode')->willReturn($protectedCode);
        $metaDataMock = $this->createMock(\Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class);
        $metaDataMock->expects($this->once())->method('setPath')
            ->with(Guest::COOKIE_PATH)
            ->willReturnSelf();
        $metaDataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($metaDataMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(Guest::COOKIE_NAME, $this->anything(), $metaDataMock);
        $this->assertTrue($this->guest->loadValidOrder($requestMock));
    }

    public function testLoadValidOrderStoredCookie()
    {
        $protectedCode = 'protectedCode';
        $incrementId = 1;
        $cookieData = $protectedCode . ':' . $incrementId;
        $cookieDataHash = base64_encode($cookieData);
        $this->sessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->orderFactoryMock->expects($this->once())->method('create')->willReturn($this->salesOrderMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Guest::COOKIE_NAME)
            ->willReturn($cookieDataHash);
        $this->salesOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($incrementId)
            ->willReturnSelf();
        $this->salesOrderMock->expects($this->exactly(1))->method('getId')->willReturn($incrementId);
        $this->salesOrderMock->expects($this->once())->method('getProtectCode')->willReturn($protectedCode);
        $metaDataMock = $this->createMock(\Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class);
        $metaDataMock->expects($this->once())
            ->method('setPath')
            ->with(Guest::COOKIE_PATH)
            ->willReturnSelf();
        $metaDataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($metaDataMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(Guest::COOKIE_NAME, $this->anything(), $metaDataMock);
        $requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->assertTrue($this->guest->loadValidOrder($requestMock));
    }
}
