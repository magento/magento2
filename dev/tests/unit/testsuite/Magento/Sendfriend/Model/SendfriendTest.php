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

namespace Magento\Sendfriend\Model;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test Sendfriend
 *
 */
class SendfriendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sendfriend\Model\Sendfriend
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Stdlib\CookieManager
     */
    protected $cookieManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sendfriendDataMock;

    public function setUp()
    {

        $objectManager = new ObjectManager($this);
        $this->sendfriendDataMock = $this->getMockBuilder('Magento\Sendfriend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManagerMock = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManager')
            ->disableOriginalConstructor()->getMock();

        $this->model = $objectManager->getObject(
            'Magento\Sendfriend\Model\Sendfriend',
            [
                'sendfriendData' => $this->sendfriendDataMock,
                'cookieManager' => $this->cookieManagerMock,
            ]
        );

    }

    public function testGetSentCountWithCheckCookie()
    {
        $cookieName = 'testCookieName';
        $this->sendfriendDataMock->expects($this->once())->method('getLimitBy')->with()->will(
            $this->returnValue(\Magento\Sendfriend\Helper\Data::CHECK_COOKIE)
        );
        $this->sendfriendDataMock->expects($this->once())->method('getCookieName')->with()->will(
            $this->returnValue($cookieName)
        );

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with($cookieName);
        $this->assertEquals(0, $this->model->getSentCount());
    }

    public function testSentCountByCookies()
    {
        $cookieName = 'testCookieName';
        $this->sendfriendDataMock->expects($this->once())->method('getCookieName')->with()->will(
            $this->returnValue($cookieName)
        );

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with($cookieName);
        $this->cookieManagerMock->expects($this->once())->method('setSensitiveCookie');
        $sendFriendClass = new \ReflectionClass('Magento\Sendfriend\Model\Sendfriend');
        $method = $sendFriendClass->getMethod('_sentCountByCookies');
        $method->setAccessible(true);
        $method->invokeArgs($this->model, [true]);
    }
}
