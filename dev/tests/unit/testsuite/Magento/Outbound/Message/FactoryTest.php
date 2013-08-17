<?php
/**
 * Magento_Outbound_Message_Factory
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
 * @category    Magento
 * @package     Magento_Outbound
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Outbound_Message_FactoryTest extends PHPUnit_Framework_TestCase
{
    const ENDPOINT_URL = 'https://endpoint_url';

    const TOPIC = 'topic';

    const CONTENT_TYPE = 'content_type';

    const AUTH_TYPE = 'auth_type';

    const FORMATTED_BODY = 'some_formatted_body';

    const TIMEOUT = 777;

    public static $body = array('some_body');

    public static $signatureHeaders = array('signature' => 'hash');

    /** @var Magento_Outbound_Message_Factory */
    protected $_factory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockObjectManager;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockFormatFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockFormatter;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockAuthFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockEndpoint;

    public function setUp()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_mockFormatFactory = $this->getMockBuilder('Magento_Outbound_Formatter_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockAuthFactory = $this->getMockBuilder('Magento_Outbound_Authentication_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_factory = new Magento_Outbound_Message_Factory($this->_mockObjectManager,
                                                               $this->_mockFormatFactory,
                                                               $this->_mockAuthFactory);

        $this->_mockFormatter = $this->getMockBuilder('Magento_Outbound_FormatterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockEndpoint = $this->getMockBuilder('Magento_Outbound_EndpointInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockEndpoint->expects($this->once())
            ->method('getFormat')
            ->will($this->returnValue('some_format'));

        $this->_mockFormatFactory->expects($this->once())
            ->method('getFormatter')
            ->with($this->equalTo('some_format'))
            ->will($this->returnValue($this->_mockFormatter));

        $this->_mockFormatter->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue(self::CONTENT_TYPE));

        $this->_mockFormatter->expects($this->once())
            ->method('format')
            ->with($this->equalTo(self::$body))
            ->will($this->returnValue(self::FORMATTED_BODY));

        $this->_mockEndpoint->expects($this->once())
            ->method('getAuthenticationType')
            ->will($this->returnValue(self::AUTH_TYPE));

        $mockAuth = $this->getMockBuilder('Magento_Outbound_AuthenticationInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockAuthFactory->expects($this->once())
            ->method('getAuthentication')
            ->with($this->equalTo(self::AUTH_TYPE))
            ->will($this->returnValue($mockAuth));

        $mockUser = $this->getMockBuilder('Magento_Outbound_UserInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockEndpoint->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($mockUser));

        $mockAuth->expects($this->once())
            ->method('getSignatureHeaders')
            ->with($this->equalTo(self::FORMATTED_BODY), $this->equalTo($mockUser))
            ->will($this->returnValue(self::$signatureHeaders));

        $this->_mockEndpoint->expects($this->once())
            ->method('getEndpointUrl')
            ->will($this->returnValue(self::ENDPOINT_URL));

        $this->_mockEndpoint->expects($this->once())
            ->method('getTimeoutInSecs')
            ->will($this->returnValue(self::TIMEOUT));

        $this->_mockObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento_Outbound_Message'), $this->anything())
            ->will($this->returnCallback(array($this, 'verifyManagerCreate')));
    }

    public function testCreateByData()
    {
        $this->assertEquals('SUCCESS', $this->_factory->createByData($this->_mockEndpoint, self::TOPIC, self::$body));
    }

    public function testCreate()
    {
        $mockEvent = $this->getMockBuilder('Magento_PubSub_EventInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('getTopic')
            ->will($this->returnValue(self::TOPIC));

        $mockEvent->expects($this->once())
            ->method('getBodyData')
            ->will($this->returnValue(self::$body));

        $this->assertEquals('SUCCESS', $this->_factory->create($this->_mockEndpoint, $mockEvent));
    }

    /**
     * Used to verify the correct arguments are passed in to Magento_ObjectManager::create
     *
     * @param       $className
     * @param array $arguments
     *
     * @return string
     */
    public function verifyManagerCreate($className, array $arguments)
    {
        $this->assertSame('Magento_Outbound_Message', $className);

        $this->assertCount(4, $arguments);

        $this->assertArrayHasKey('endpointUrl', $arguments);
        $this->assertSame(self::ENDPOINT_URL, $arguments['endpointUrl']);

        $this->assertArrayHasKey('headers', $arguments);
        $headers = $arguments['headers'];
        $this->assertArrayHasKey(Magento_Outbound_Message_FactoryInterface::TOPIC_HEADER, $headers);
        $this->assertSame(self::TOPIC, $headers[Magento_Outbound_Message_FactoryInterface::TOPIC_HEADER]);
        $this->assertArrayHasKey(Magento_Outbound_FormatterInterface::CONTENT_TYPE_HEADER, $headers);
        $this->assertSame(self::CONTENT_TYPE, $headers[Magento_Outbound_FormatterInterface::CONTENT_TYPE_HEADER]);
        foreach (self::$signatureHeaders as $key => $value) {
            $this->assertArrayHasKey($key, $headers);
            $this->assertSame($value, $headers[$key]);
        }

        $this->assertArrayHasKey('body', $arguments);
        $this->assertSame(self::FORMATTED_BODY, $arguments['body']);

        $this->assertArrayHasKey('timeout', $arguments);
        $this->assertSame(self::TIMEOUT, $arguments['timeout']);

        return 'SUCCESS';
    }
}
