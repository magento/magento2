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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Authentication_Hmac_Sha256Test extends PHPUnit_Framework_TestCase
{

    /**
     * @var Mage_Webhook_Model_Authentication_Interface
     */
    public $signer;

    /**
     * A random 32 byte string
     */
    const SHARED_SECRET = 'x0lpu8kcmu23l8jcqd7qmyknyl5kx2f9';

    const BODY = 'This is a test body and has no semantic value.';

    const DOMAIN = 'www.fake.magento.com';

    /**
     * @var Mage_Webhook_Model_Subscriber
     */
    public $mockSubscriber;

    /**
     * @var Mage_Webapi_Model_Acl_User
     */
    public $mockApiUser;

    public function setUp()
    {
        $this->signer = $this->getMock('Mage_Webhook_Model_Authentication_Hmac_Sha256', array('_getDomain'));
        $this->signer->expects($this->once())->method('_getDomain')->will($this->returnValue(self::DOMAIN));
    }

    public function testHeaders()
    {
        $this->mockApiUser =
                $this->getMockBuilder('Mage_Webapi_Model_Acl_User')->disableOriginalConstructor()
                        ->setMethods(array('getSecret'))->getMock();
        $this->mockApiUser->expects($this->once())->method('getSecret')->will($this->returnValue(self::SHARED_SECRET));

        $this->mockSubscriber =
                $this->getMockBuilder('Mage_Webhook_Model_Subscriber')->disableOriginalConstructor()->getMock();
        $this->mockSubscriber->expects($this->once())->method('getApiUser')
                ->will($this->returnValue($this->mockApiUser));

        $request = new Mage_Webhook_Model_Transport_Http_Request();
        $request->setBody(self::BODY);
        $this->assertCount(0, $request->getHeaders());

        $headers = $this->signer->signRequest($request, $this->mockSubscriber)->getHeaders();

        $this->assertCount(2, $request->getHeaders());
        $this->assertContains(Mage_Webhook_Model_Authentication_Abstract::DOMAIN_HEADER, array_keys($headers));
        $this->assertContains(Mage_Webhook_Model_Authentication_Hmac::HMAC_HEADER, array_keys($headers));
        $this->assertEquals(self::DOMAIN, $headers[Mage_Webhook_Model_Authentication_Abstract::DOMAIN_HEADER]);
        $this->assertEquals(
            hash_hmac($this->signer->getHashAlgorithm(), self::BODY, self::SHARED_SECRET),
            $headers[Mage_Webhook_Model_Authentication_Hmac::HMAC_HEADER]
        );
    }

    /**
     * @expectedException LogicException
     * @expectedMessage The shared secret cannot be a empty.
     */
    public function testEmptySecret()
    {
        $this->mockApiUser =
                $this->getMockBuilder('Mage_Webapi_Model_Acl_User')->disableOriginalConstructor()
                        ->setMethods(array('getSecret'))->getMock();
        $this->mockApiUser->expects($this->once())->method('getSecret')->will($this->returnValue(""));

        $this->mockSubscriber =
                $this->getMockBuilder('Mage_Webhook_Model_Subscriber')->disableOriginalConstructor()->getMock();
        $this->mockSubscriber->expects($this->once())->method('getApiUser')
                ->will($this->returnValue($this->mockApiUser));

        $request = new Mage_Webhook_Model_Transport_Http_Request();
        $request->setBody(self::BODY);
        $this->assertCount(0, $request->getHeaders());

        $this->signer->signRequest($request, $this->mockSubscriber)->getHeaders();
    }
}
