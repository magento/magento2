<?php
/**
 * \Magento\Webhook\Model\Endpoint
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
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

class EndpointTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockObjectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockUserFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Webhook\Model\Endpoint */
    protected $_endpoint;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockResourceEndpnt;

    protected function setUp()
    {
        $this->_mockResourceEndpnt = $this->getMockBuilder('Magento\Webhook\Model\Resource\Endpoint')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockUserFactory = $this->getMockBuilder('Magento\Webhook\Model\User\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockContext = $this->getMockBuilder('Magento\Core\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetters()
    {
        $endpointUrl = 'https://endpoint_url';
        $timeoutInSeconds = '357';
        $format = 'presumambly_json';
        $authenticationType = 'hmac';
        $apiUsedId = '747';

        $mockWebhookUser = $this->getMockBuilder('Magento\Webhook\Model\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockUserFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($apiUsedId))
            ->will($this->returnValue($mockWebhookUser));

        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        // we have to use a mock because ancestor code utilizes deprecated static methods
        $this->_endpoint = $this->getMockBuilder('Magento\Webhook\Model\Endpoint')
            ->setConstructorArgs(array(
                $this->_mockContext,
                $coreRegistry,
                $this->_mockUserFactory
            ))
            ->setMethods(array('_init'))
            ->getMock();

        $this->_endpoint->setEndpointUrl($endpointUrl)
            ->setTimeoutInSecs($timeoutInSeconds)
            ->setFormat($format)
            ->setAuthenticationType($authenticationType)
            ->setApiUserId($apiUsedId);

        $this->assertSame($endpointUrl, $this->_endpoint->getEndpointUrl());
        $this->assertSame($timeoutInSeconds, $this->_endpoint->getTimeoutInSecs());
        $this->assertSame($format, $this->_endpoint->getFormat());
        $this->assertSame($authenticationType, $this->_endpoint->getAuthenticationType());
        $this->assertSame($mockWebhookUser, $this->_endpoint->getUser());
    }

    /**
     * Generates all possible combinations of two boolean values
     *
     * @return array of arrays of booleans
     */
    public function testBeforeSaveDataProvider()
    {
        return array(
            array(false, false),
            array(false, true),
            array(true, false),
            array(true, true)
        );
    }

    /**
     * @dataProvider testBeforeSaveDataProvider
     *
     * @param $hasAuthType
     * @param $hasDataChanges
     */
    public function testBeforeSave($hasAuthType, $hasDataChanges)
    {
        $mockEventManager = $this->getMockBuilder('Magento\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockContext->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($mockEventManager));

        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        // we have to use a mock because ancestor code utilizes deprecated static methods
        $this->_endpoint = $this->getMockBuilder('Magento\Webhook\Model\Endpoint')
            ->setConstructorArgs(array(
                $this->_mockContext,
                $coreRegistry,
                $this->_mockUserFactory,
            ))
            ->setMethods(
                array('_init', '_getResource', 'hasAuthenticationType', 'setAuthenticationType', 'setUpdatedAt',
                      'isDeleted', '_hasModelChanged')
            )
            ->getMock();

        $this->_mockMethodsForSaveCall();

        $this->_endpoint->expects($this->once())
            ->method('hasAuthenticationType')
            ->will($this->returnValue($hasAuthType));

        if (!$hasAuthType) {
            $this->_endpoint->expects($this->once())
                ->method('setAuthenticationType')
                ->with($this->equalTo(\Magento\Outbound\EndpointInterface::AUTH_TYPE_NONE));
        } else {
            $this->_endpoint->expects($this->never())
                ->method('setAuthenticationType');
        }

        $this->_endpoint->setDataChanges($hasDataChanges);

        if ($hasDataChanges) {
            $someFormattedTime = '2013-07-10 12:35:28';
            $this->_mockResourceEndpnt->expects($this->once())
                ->method('formatDate')
                ->withAnyParameters() // impossible to predict what time() will be
                ->will($this->returnValue($someFormattedTime));
            $this->_endpoint->expects($this->once())
                ->method('setUpdatedAt')
                ->with($this->equalTo($someFormattedTime));
        } else {
            $this->_endpoint->expects($this->never())
                ->method('setUpdatedAt');
        }

        $this->assertSame($this->_endpoint, $this->_endpoint->save());
    }

    /**
     * This mocks the methods called in the save() method such that beforeSave()
     * will be called and no errors will be produced during the save() call
     * See \Magento\Core\Model\AbstractModel::save() for details
     */
    private function _mockMethodsForSaveCall()
    {
        $this->_endpoint->expects($this->any())
            ->method('isDeleted')
            ->will($this->returnValue(false));

        $this->_endpoint->expects($this->any())
            ->method('_hasModelChanged')
            ->will($this->returnValue(true));

        $this->_endpoint->expects($this->any())
            ->method('_getResource')
            ->will($this->returnValue($this->_mockResourceEndpnt));

        $abstractMockResource = $this->getMockBuilder('Magento\Webhook\Model\Resource\Endpoint')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockResourceEndpnt->expects($this->any())
            ->method('addCommitCallback')
            ->withAnyParameters()
            ->will($this->returnValue($abstractMockResource));
    }
}
