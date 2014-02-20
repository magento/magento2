<?php
/**
 * \Magento\Outbound\Authentication\Hmac
 *
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
namespace Magento\Outbound\Authentication;

class HmacTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Outbound\Authentication\Hmac
     */
    private $_model;

    /**
     * A random 32 byte string
     */
    const SHARED_SECRET = 'x0lpu8kcmu23l8jcqd7qmyknyl5kx2f9';

    /** message body */
    const BODY = 'This is a test body and has no semantic value.';

    /** message head domain */
    const DOMAIN = 'www.fake.magento.com';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject \Magento\Outbound\MessageInterface
     */
    private $_mockMessage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject \Magento\Outbound\UserInterface
     */
    private $_mockUser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject \Magento\Core\Model\StoreManagerInterface
     */
    private $_mockStoreManager;

    protected function setUp()
    {
        $this->_mockStoreManager = $this->getMockBuilder('Magento\Core\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new \Magento\Outbound\Authentication\Hmac($this->_mockStoreManager);

        $this->_mockMessage = $this->getMockBuilder('Magento\Outbound\MessageInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockMessage->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue(self::BODY));

        $this->_mockUser = $this->getMockBuilder('Magento\Outbound\UserInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHeaders()
    {
        $store = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockStoreManager->expects($this->once())
            ->method('getSafeStore')
            ->will($this->returnValue($store));
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://' . self::DOMAIN));
        
        $this->_mockUser->expects($this->once())
            ->method('getSharedSecret')
            ->will($this->returnValue(self::SHARED_SECRET));

        $hash = (string) hash_hmac(
            \Magento\Outbound\Authentication\Hmac::SHA256_ALGORITHM,
            self::BODY,
            self::SHARED_SECRET
        );

        $headers = $this->_model->getSignatureHeaders($this->_mockMessage->getBody(), $this->_mockUser);
        $this->assertArrayHasKey(\Magento\Outbound\Authentication\Hmac::DOMAIN_HEADER, $headers);
        $this->assertSame(self::DOMAIN, $headers[\Magento\Outbound\Authentication\Hmac::DOMAIN_HEADER]);
        $this->assertArrayHasKey(\Magento\Outbound\Authentication\Hmac::HMAC_HEADER, $headers);
        $this->assertSame($hash, $headers[\Magento\Outbound\Authentication\Hmac::HMAC_HEADER]);

    }

    /**
     * @expectedException \LogicException
     * @expectedMessage The shared secret cannot be a empty.
     */
    public function testEmptySecret()
    {
        $this->_mockUser->expects($this->once())
            ->method('getSharedSecret')
            ->will($this->returnValue(''));

        $this->_model->getSignatureHeaders($this->_mockMessage, $this->_mockUser);
    }
}
