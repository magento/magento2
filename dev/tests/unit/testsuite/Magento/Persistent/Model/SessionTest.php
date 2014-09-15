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
namespace Magento\Persistent\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\Stdlib\CookieManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->configMock = $this->getMock('Magento\Framework\Session\Config\ConfigInterface');
        $this->cookieManagerMock = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        )->disableOriginalConstructor()
            ->getMock();

        $resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [],
            '',
            false,
            false,
            true,
            ['__wakeup', 'getIdFieldName', 'getConnection', 'beginTransaction', 'delete', 'commit', 'rollBack']
        );

        $actionValidatorMock = $this->getMock(
            'Magento\Framework\Model\ActionValidator\RemoveAction',
            [],
            [],
            '',
            false
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $context = $helper->getObject(
            'Magento\Framework\Model\Context',
            [
                'actionValidator' => $actionValidatorMock,
            ]
        );

        $this->session = $helper->getObject(
            'Magento\Persistent\Model\Session',
            [
                'sessionConfig' => $this->configMock,
                'cookieManager' => $this->cookieManagerMock,
                'context'       => $context,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'resource' => $resourceMock,
            ]
        );
    }

    public function testLoadByCookieKeyWithNull()
    {
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(\Magento\Persistent\Model\Session::COOKIE_NAME)
            ->will($this->returnValue(null));
        $this->session->loadByCookieKey(null);
    }

    /**
     * @covers \Magento\Persistent\Model\Session::_afterDeleteCommit
     * @covers \Magento\Persistent\Model\Session::removePersistentCookie
     */
    public function testAfterDeleteCommit()
    {
        $cookiePath = 'some_path';
        $this->configMock->expects($this->once())->method('getCookiePath')->will($this->returnValue($cookiePath));
        $cookieMetadataMock = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\CookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with($cookiePath)
            ->will($this->returnSelf());
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createCookieMetadata')
            ->will($this->returnValue($cookieMetadataMock));
        $this->cookieManagerMock->expects(
            $this->once()
        )->method(
            'deleteCookie'
        )->with(
            \Magento\Persistent\Model\Session::COOKIE_NAME,
            $cookieMetadataMock
        );
        $this->session->delete();
    }

    public function testSetPersistentCookie()
    {
        $cookiePath = 'some_path';
        $duration = 1000;
        $key = 'sessionKey';
        $this->session->setKey($key);
        $cookieMetadataMock = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\PublicCookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with($cookiePath)
            ->will($this->returnSelf());
        $cookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->with($duration)
            ->will($this->returnSelf());
        $cookieMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->will($this->returnSelf());
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->will($this->returnValue($cookieMetadataMock));
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                \Magento\Persistent\Model\Session::COOKIE_NAME,
                $key,
                $cookieMetadataMock
            );
        $this->session->setPersistentCookie($duration, $cookiePath);
    }

    /**
     * @param $numGetCookieCalls
     * @param $numCalls
     * @param int $cookieDuration
     * @param string $cookieValue
     * @param string $cookiePath
     * @dataProvider renewPersistentCookieDataProvider
     */
    public function testRenewPersistentCookie(
        $numGetCookieCalls,
        $numCalls,
        $cookieDuration = 1000,
        $cookieValue = 'cookieValue',
        $cookiePath = 'cookiePath'
    ) {
        $cookieMetadataMock = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\PublicCookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setPath')
            ->with($cookiePath)
            ->will($this->returnSelf());
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setDuration')
            ->with($cookieDuration)
            ->will($this->returnSelf());
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setHttpOnly')
            ->with(true)
            ->will($this->returnSelf());
        $this->cookieMetadataFactoryMock->expects($this->exactly($numCalls))
            ->method('createPublicCookieMetadata')
            ->will($this->returnValue($cookieMetadataMock));
        $this->cookieManagerMock->expects($this->exactly($numGetCookieCalls))
            ->method('getCookie')
            ->with(\Magento\Persistent\Model\Session::COOKIE_NAME)
            ->will($this->returnValue($cookieValue));
        $this->cookieManagerMock->expects($this->exactly($numCalls))
            ->method('setPublicCookie')
            ->with(
                \Magento\Persistent\Model\Session::COOKIE_NAME,
                $cookieValue,
                $cookieMetadataMock
            );
        $this->session->renewPersistentCookie($cookieDuration, $cookiePath);
    }

    /**
     * Data provider for testRenewPersistentCookie
     *
     * @return array
     */
    public function renewPersistentCookieDataProvider()
    {
        return [
            'no duration' => [0, 0, null ],
            'no cookie' => [1, 0, 1000, null],
            'all' => [1, 1, ],
        ];
    }
}
