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
class Mage_Webhook_Model_Authentication_NoneTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Authentication_Interface
     */
    public $signer;

    const BODY = 'This is a test body and has no semantic value.';

    const DOMAIN = 'www.fake.magento.com';

    /**
     * @var Mage_Webhook_Model_Subscriber
     */
    public $mockSubscriber;

    public function setUp()
    {
        $this->signer = $this->getMock('Mage_Webhook_Model_Authentication_None', array('_getDomain'));
        $this->signer->expects($this->once())->method('_getDomain')->will($this->returnValue(self::DOMAIN));
        $this->mockSubscriber =
                $this->getMockBuilder('Mage_Webhook_Model_Subscriber')->disableOriginalConstructor()->getMock();
    }

    public function testDomainHeaderIsSet()
    {
        $request = new Mage_Webhook_Model_Transport_Http_Request();
        $request->setBody(self::BODY);
        $this->assertCount(0, $request->getHeaders());

        $headers = $this->signer->signRequest($request, $this->mockSubscriber)->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertContains(Mage_Webhook_Model_Authentication_Abstract::DOMAIN_HEADER,
                              array_keys($headers));
        $this->assertEquals(self::DOMAIN, $headers[Mage_Webhook_Model_Authentication_Abstract::DOMAIN_HEADER]);
    }
}
