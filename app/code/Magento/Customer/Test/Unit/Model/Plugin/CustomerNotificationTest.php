<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Plugin\CustomerNotification;

class CustomerNotificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Customer\Model\Customer\NotificationStorage|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationStorage;

    /** @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieManager;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieMetadataFactory;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Backend\App\AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    protected $abstractAction;

    /** @var CustomerNotification */
    protected $plugin;

    protected function setUp()
    {
        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->notificationStorage = $this->getMockBuilder('Magento\Customer\Model\Customer\NotificationStorage')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManager = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManagerInterface')
            ->getMockForAbstractClass();
        $this->cookieMetadataFactory = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractAction = $this->getMockBuilder('Magento\Backend\App\AbstractAction')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')->getMockForAbstractClass();
        $this->appState = $this->getMockBuilder('Magento\Framework\App\State')->disableOriginalConstructor()->getMock();
        $this->plugin = new CustomerNotification(
            $this->session,
            $this->notificationStorage,
            $this->cookieManager,
            $this->cookieMetadataFactory,
            $this->appState
        );
    }

    public function testBeforeDispatch()
    {
        $customerId = 1;
        $this->appState->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->session->expects($this->any())->method('getCustomerId')->willReturn($customerId);
        $this->notificationStorage->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId)
            ->willReturn(true);

        $publicCookieMetadata = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\PublicCookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $publicCookieMetadata->expects($this->once())->method('setPath')->with('/');
        $publicCookieMetadata->expects($this->once())->method('setDurationOneYear');
        $publicCookieMetadata->expects($this->once())->method('setHttpOnly')->with(false);

        $sensitiveCookieMetadata = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $sensitiveCookieMetadata->expects($this->once())->method('setPath')->with('/')->willReturnSelf();

        $this->cookieMetadataFactory->expects($this->any())
            ->method('createPublicCookieMetadata')
            ->willReturn($publicCookieMetadata);
        $this->cookieMetadataFactory->expects($this->any())
            ->method('createSensitiveCookieMetadata')
            ->willReturn($sensitiveCookieMetadata);

        $this->cookieManager->expects($this->once())
            ->method('setPublicCookie')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId, $publicCookieMetadata);
        $this->cookieManager->expects($this->once())
            ->method('deleteCookie')
            ->with(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING, $sensitiveCookieMetadata);

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }
}
