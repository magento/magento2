<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Helper;

use Magento\Sales\Helper\Guest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestTest extends \PHPUnit_Framework_TestCase
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
        $this->appContextHelperMock = $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false);
        $this->storeManagerInterfaceMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class);
        $this->sessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->cookieManagerMock = $this->getMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class,
            [],
            [],
            '',
            false
        );
        $this->managerInterfaceMock = $this->getMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->orderFactoryMock = $this->getMock(\Magento\Sales\Model\OrderFactory::class, ['create'], [], '', false);
        $this->viewInterfaceMock = $this->getMock(\Magento\Framework\App\ViewInterface::class);
        $this->storeModelMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesOrderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [
                'getProtectCode', 'loadByIncrementIdAndStoreId', 'loadByIncrementId',
                'getId', 'getBillingAddress', '__wakeup'
            ],
            [],
            '',
            false
        );

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
        $requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $requestMock->expects($this->once())->method('getPostValue')->willReturn($post);
        $this->storeManagerInterfaceMock->expects($this->once())->method('getStore')->willReturn($this->storeModelMock);
        $this->storeModelMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->orderFactoryMock->expects($this->once())->method('create')->willReturn($this->salesOrderMock);
        $this->salesOrderMock->expects($this->once())->method('loadByIncrementIdAndStoreId')->willReturnSelf();
        $this->salesOrderMock->expects($this->any())->method('getId')->willReturn($incrementId);

        $billingAddressMock = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getLastname', 'getEmail', '__wakeup'],
            [],
            '',
            false
        );
        $billingAddressMock->expects($this->once())->method('getLastname')->willReturn(($post['oar_billing_lastname']));
        $billingAddressMock->expects($this->once())->method('getEmail')->willReturn(($post['oar_email']));
        $this->salesOrderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->salesOrderMock->expects($this->once())->method('getProtectCode')->willReturn($protectedCode);
        $metaDataMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class,
            [],
            [],
            '',
            false
        );
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
        $metaDataMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class,
            [],
            [],
            '',
            false
        );
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
        $requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->assertTrue($this->guest->loadValidOrder($requestMock));
    }
}
