<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Helper;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class GuestTest
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

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigInterfaceMock;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerInterfaceMock;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $stateMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\Stdlib\CookieManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieManagerMock;
    
    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieMetadataFactoryMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $orderFactoryMock;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewInterfaceMock;

    protected function setUp()
    {
        $this->appContextHelperMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->scopeConfigInterfaceMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->storeManagerInterfaceMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->stateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->registryMock = $this->getMock('Magento\Framework\Registry');
        $this->sessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManager', [], [], '', false);
        $this->cookieMetadataFactoryMock = $this->getMock(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory',
            [],
            [],
            '',
            false
        );
        $this->managerInterfaceMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->viewInterfaceMock = $this->getMock('Magento\Framework\App\ViewInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->guest = $this->objectManagerHelper->getObject(
            'Magento\Sales\Helper\Guest',
            [
                'context' => $this->appContextHelperMock,
                'scopeConfig' => $this->scopeConfigInterfaceMock,
                'storeManager' => $this->storeManagerInterfaceMock,
                'appState' => $this->stateMock,
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
        $this->sessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));

        $post = [
            'oar_order_id' => 1,
            'oar_type' => 'email',
            'oar_billing_lastname' => 'oar_billing_lastname',
            'oar_email' => 'oar_email',
            'oar_zip' => 'oar_zip'

        ];
        $incrementId = $post['oar_order_id'];
        $requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $requestMock->expects($this->once())->method('getPost')->will($this->returnValue($post));

        $orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getProtectCode', 'loadByIncrementId', 'getId', 'getBillingAddress', '__wakeup'],
            [],
            '',
            false
        );
        $this->orderFactoryMock->expects($this->once())->method('create')->will($this->returnValue($orderMock));
        $orderMock->expects($this->once())->method('loadByIncrementId')->with($incrementId);
        $orderMock->expects($this->exactly(2))->method('getId')->will($this->returnValue($incrementId));

        $billingAddressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            ['getLastname', 'getEmail', '__wakeup'],
            [],
            '',
            false
        );
        $billingAddressMock->expects($this->once())->method('getLastname')->will(
            $this->returnValue($post['oar_billing_lastname'])
        );
        $billingAddressMock->expects($this->once())->method('getEmail')->will(
            $this->returnValue($post['oar_email'])
        );
        $orderMock->expects($this->once())->method('getBillingAddress')->will($this->returnValue($billingAddressMock));
        $protectedCode = 'protectedCode';
        $orderMock->expects($this->once())->method('getProtectCode')->will($this->returnValue($protectedCode));
        $metaDataMock = $this->getMock(
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata',
            [],
            [],
            '',
            false
        );
        $metaDataMock->expects($this->once())
            ->method('setPath')
            ->with(Guest::COOKIE_PATH)
            ->will($this->returnSelf());
        $metaDataMock->expects($this->once())
            ->method('setDuration')
            ->with(Guest::COOKIE_LIFETIME)
            ->will($this->returnSelf());
        $metaDataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->will($this->returnSelf());
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->will($this->returnValue($metaDataMock));
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(Guest::COOKIE_NAME, $this->anything(), $metaDataMock);
        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->assertTrue($this->guest->loadValidOrder($requestMock, $responseMock));
    }

    public function testLoadValidOrderStoredCookie()
    {
        $this->sessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getProtectCode', 'loadByIncrementId', 'getId', 'getBillingAddress', '__wakeup'],
            [],
            '',
            false
        );
        $protectedCode = 'protectedCode';
        $incrementId = 1;
        $cookieData = $protectedCode . ':' . $incrementId;
        $cookieDataHash = base64_encode($cookieData);
        $this->orderFactoryMock->expects($this->once())->method('create')->will($this->returnValue($orderMock));

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with(Guest::COOKIE_NAME)->will(
            $this->returnValue($cookieDataHash)
        );
        $orderMock->expects($this->once())->method('loadByIncrementId')->with($incrementId);
        $orderMock->expects($this->exactly(1))->method('getId')->will($this->returnValue($incrementId));
        $orderMock->expects($this->once())->method('getProtectCode')->will($this->returnValue($protectedCode));
        $metaDataMock = $this->getMock(
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata',
            [],
            [],
            '',
            false
        );
        $metaDataMock->expects($this->once())
            ->method('setPath')
            ->with(Guest::COOKIE_PATH)
            ->will($this->returnSelf());
        $metaDataMock->expects($this->once())
            ->method('setDuration')
            ->with(Guest::COOKIE_LIFETIME)
            ->will($this->returnSelf());
        $metaDataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->will($this->returnSelf());
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->will($this->returnValue($metaDataMock));
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(Guest::COOKIE_NAME, $this->anything(), $metaDataMock);

        $requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->assertTrue($this->guest->loadValidOrder($requestMock, $responseMock));
    }
}
