<?php
/**
 * \Magento\Webhook\Model\Subscription
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

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Unit under test
     *
     * @var \Magento\Webhook\Model\Subscription|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subscription;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockEndpoint;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockUser;

    protected function setUp()
    {
        $this->_mockEndpoint = $this->getMockBuilder('Magento\Webhook\Model\Endpoint')
            ->setMethods(array('_init', 'save', 'setEndpointId', 'getId', 'getUser', '_getResource', 'delete',
                'load', 'hasDataChanges'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockUser = $this->getMockBuilder('Magento\Webhook\Model\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockEndpoint->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->_mockUser));

        $mockEventDispatcher = $this->getMockBuilder('Magento\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockContext = $this->getMockBuilder('Magento\Core\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockContext->expects($this->any())
            ->method('getEventDispatcher')
            ->withAnyParameters()
            ->will($this->returnValue($mockEventDispatcher));

        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        $this->_subscription = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->setMethods(array('_init', '_hasModelChanged', '_getResource'))
            ->setConstructorArgs(array($this->_mockEndpoint, $this->_mockContext, $coreRegistry))
            ->getMock();

        $subscriptionResource = $this->getMockBuilder('Magento\Webhook\Model\Resource\Subscription')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_subscription->expects($this->any())
            ->method('_getResource')
            ->will($this->returnValue($subscriptionResource));
    }

    public function testFindRestrictedTopics()
    {
        $this->_mockUser->expects($this->atLeastOnce())
            ->method('hasPermission')
            ->will(
                $this->returnValueMap(
                    array(
                         array('restricted', false),
                         array('allowed', true),
                    )
                )
            );

        $this->_subscription->setTopics(array('restricted', 'allowed'));

        $restrictedTopics = $this->_subscription->findRestrictedTopics();

        $this->assertEquals(array('restricted'), $restrictedTopics);
    }

    public function testFindRestrictedTopicsWithNoUser()
    {
        // The only way to override a pre-existing implementation is to create a new object
        $this->_mockEndpoint = $this->getMockBuilder('Magento\Webhook\Model\Endpoint')
            ->setMethods(array('_init', 'save', 'setEndpointId', 'getId', 'getUser', '_getResource', 'delete'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockEndpoint->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(null));

        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        $this->_subscription = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->setMethods(array('_init', '_hasModelChanged', '_getResource'))
            ->setConstructorArgs(array($this->_mockEndpoint, $this->_mockContext, $coreRegistry))
            ->getMock();

        $this->_subscription->setTopics(array('restricted', 'allowed'));

        $restrictedTopics = $this->_subscription->findRestrictedTopics();

        $this->assertEmpty($restrictedTopics);
    }

    public function testAfterDelete()
    {
        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        // it's useful to mock out more methods for the purposes of testing this one method
        $this->_subscription = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->setMethods(
                array('hasStatus', 'setStatus', 'hasRegistrationMechanism',
                    'setRegistrationMechanism', 'getEndpointId', 'setEndpointId', 'setUpdatedAt',
                    'hasDataChanges', '_init', '_hasModelChanged', '_getResource')
            )
            ->setConstructorArgs(array($this->_mockEndpoint, $this->_mockContext, $coreRegistry))
            ->getMock();

        $mockResource = $this->getMockBuilder('Magento\Webhook\Model\Resource\Subscription')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_subscription->expects($this->any())
            ->method('_getResource')
            ->withAnyParameters()
            ->will($this->returnValue($mockResource));

        $this->_mockEndpoint->expects($this->once())
            ->method('delete');

        $this->_subscription->setEndpointUrl('http://localhost');
        $this->_subscription->delete();
    }

    /**
     * @dataProvider beforeSaveDataProvider
     *
     * @param $hasRegiMechanism
     * @param $hasDataChanges
     * @param $hasEndpointChanges
     * @param $hasEndpointId
     */
    public function testBeforeSave($hasRegiMechanism, $hasEndpointChanges, $hasEndpointId, $hasDataChanges)
    {
        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        // it's useful to mock out more methods for the purposes of testing this one method
        $this->_subscription = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->setMethods(
                array('hasStatus', 'setStatus', 'hasRegistrationMechanism', 'setRegistrationMechanism', 'getEndpointId',
                      'setEndpointId', 'setUpdatedAt', 'hasDataChanges', '_init', '_hasModelChanged', '_getResource')
            )
            ->setConstructorArgs(array($this->_mockEndpoint, $this->_mockContext, $coreRegistry))
            ->getMock();

        $mockResource = $this->getMockBuilder('Magento\Webhook\Model\Resource\Subscription')
            ->disableOriginalConstructor()
            ->getMock();

        $mockResource->expects($this->once())
            ->method('addCommitCallback')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_subscription->expects($this->any())
            ->method('_getResource')
            ->withAnyParameters()
            ->will($this->returnValue($mockResource));

        $this->_subscription->expects($this->once())
            ->method('_hasModelChanged')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $this->_expectHasRegistrationMechanism($hasRegiMechanism);

        $this->_expectEndpointOrId($hasEndpointChanges, $hasEndpointId);

        $this->_expectSubscriptionHasDataChanges($hasDataChanges, $mockResource);

        $this->assertEquals($this->_subscription, $this->_subscription->save());
    }

    /**
     * Mock out the subscription depending on whether or not it will have a registration mechanism
     *
     * @param $hasRegiMechanism
     */
    protected function _expectHasRegistrationMechanism($hasRegiMechanism)
    {
        $this->_subscription->expects($this->once())
            ->method('hasRegistrationMechanism')
            ->will($this->returnValue($hasRegiMechanism));
        if (!$hasRegiMechanism) {
            $this->_subscription->expects($this->once())
                ->method('setRegistrationMechanism')
                ->with($this->equalTo(\Magento\Webhook\Model\Subscription::REGISTRATION_MECHANISM_MANUAL));
        } else {
            $this->_subscription->expects($this->never())
                ->method('setRegistrationMechanism');
        }
    }

    /**
     * Mock out the subscription depending on whether or not it will have an endpoint and/or an endpoint id
     *
     * @param $hasEndpointChanges
     * @param $hasEndpointId
     */
    protected function _expectEndpointOrId($hasEndpointChanges, $hasEndpointId)
    {
        $this->_mockEndpoint->expects($this->any())
            ->method('hasDataChanges')
            ->will($this->returnValue($hasEndpointChanges));

        if ($hasEndpointChanges) {
            $this->_subscription->expects($this->any())
                ->method('getEndpointId')
                ->will($this->returnValue($hasEndpointId ? 'id' : null));

            if ($hasEndpointId) {
                $this->_mockEndpoint->expects($this->atLeastOnce())
                    ->method('load')
                    ->with($this->equalTo('id'))
                    ->will($this->returnValue($this->_mockEndpoint));
                $this->_mockEndpoint->expects($this->never())
                    ->method('getId');
                $this->_subscription->expects($this->never())
                    ->method('setEndpointId');
            } else {
                $this->_mockEndpoint->expects($this->never())
                    ->method('load');
                $this->_mockEndpoint->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue('id'));
                $this->_subscription->expects($this->once())
                    ->method('setEndpointId')
                    ->with($this->equalTo('id'));
            }

            $this->_mockEndpoint->expects($this->once())
                ->method('save');

            // we need to make a call that will set the endpoint
            // this should end up calling the factory to create an endpoint
            $this->_subscription->getEndpointUrl();
        } else {
            $this->_mockEndpoint->expects($this->never())
                ->method('save');
            $this->_subscription->expects($this->never())
                ->method('getEndpointId');
            $this->_mockEndpoint->expects($this->never())
                ->method('getId');
            $this->_subscription->expects($this->never())
                ->method('setEndpointId');
        }
    }

    /**
     * Mock out the subscription depending on whether or not it will have data changes
     *
     * @param $hasDataChanges
     * @param $mockResource
     */
    protected function _expectSubscriptionHasDataChanges($hasDataChanges, $mockResource)
    {
        $this->_subscription->expects($this->once())
            ->method('hasDataChanges')
            ->will($this->returnValue($hasDataChanges));
        if ($hasDataChanges) {
            $someFormattedTime = '2013-07-10 12:35:28';
            $mockResource->expects($this->once())
                ->method('formatDate')
                ->withAnyParameters()
                ->will($this->returnValue($someFormattedTime));
            $this->_subscription->expects($this->once())
                ->method('setUpdatedAt')
                ->with($this->equalTo($someFormattedTime));
        } else {
            $mockResource->expects($this->never())
                ->method('formatDate');
            $this->_subscription->expects($this->never())
                ->method('setUpdatedAt');
        }
    }

    /**
     * Returns all possible arrays with 5 boolean values.
     * e.g. (true, false, true, false, true) etc.
     *
     * @return array of all possible combinations of 5 boolean values
     */
    public function beforeSaveDataProvider()
    {
        return $this->_allNBitCombinations(4);
    }

    /**
     * Generates all $arrayLength bit combinations in the format of arrays containing true/false values
     *
     * @param $arrayLength int non-negative value
     *
     * @return array all combinations of arrays with $arrayLength true/false values
     */
    protected function _allNBitCombinations($arrayLength)
    {
        $returnValue = array();
        if (0 == $arrayLength) {
            $returnValue[] = array();
        } elseif (0 < $arrayLength) {
            foreach (array(true, false) as $suffix) {
                foreach ($this->_allNBitCombinations($arrayLength - 1) as $subset) {
                    $subset[] = $suffix;
                    $returnValue[] = $subset;
                }
            }
        }
        return $returnValue;
    }

    public function testGetSetData()
    {
        $keys = array('endpoint_url', 'format', 'authentication_type', 'api_user_id', 'timeout_in_secs');
        // test individual set/get
        foreach ($keys as $key) {
            $value = 'some value';
            $this->assertNull($this->_subscription->getData($key));
            $this->assertSame($this->_subscription, $this->_subscription->setData($key, $value));
            $this->assertSame($value, $this->_subscription->getData($key));
        }

        // test setting all data
        $data = array(
            'endpoint_url'        => 'some endpoint url',
            'format'              => 'some format',
            'authentication_type' => 'some authentication type',
            'api_user_id'         => 'some api user id',
            'timeout_in_secs'     => 'some timeout in secs',
        );
        $this->assertSame($this->_subscription, $this->_subscription->setData($data));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame($data, $this->_subscription->getData());
    }

    public function testName()
    {
        $this->assertNull($this->_subscription->getName());
        $this->assertSame($this->_subscription, $this->_subscription->setName('some name'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some name', $this->_subscription->getName());
    }

    public function testEndpointId()
    {
        $this->assertNull($this->_subscription->getEndpointId());
        $this->assertSame($this->_subscription, $this->_subscription->setEndpointId('some endpoint id'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some endpoint id', $this->_subscription->getEndpointId());
    }

    public function testUpdatedAt()
    {
        $this->assertNull($this->_subscription->getUpdatedAt());
        $this->assertSame($this->_subscription, $this->_subscription->setUpdatedAt('some time'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some time', $this->_subscription->getUpdatedAt());
    }

    public function testStatus()
    {
        $this->assertTrue($this->_subscription->hasStatus());
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_INACTIVE, $this->_subscription->getStatus());
        $this->assertSame(
            $this->_subscription, $this->_subscription->setStatus(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE)
        );
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertTrue($this->_subscription->hasStatus());
        $this->assertSame(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE, $this->_subscription->getStatus());
    }

    public function testDeactivate()
    {
        $this->_subscription->deactivate();
        $this->assertFalse($this->_subscription->hasDataChanges());
        $this->assertSame(\Magento\PubSub\SubscriptionInterface::STATUS_INACTIVE, $this->_subscription->getStatus());
    }

    public function testActivateDeactivate()
    {
        $this->_subscription->activate();
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE, $this->_subscription->getStatus());

        $this->_subscription->setDataChanges(false);
        $this->_subscription->deactivate();
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame(\Magento\PubSub\SubscriptionInterface::STATUS_INACTIVE, $this->_subscription->getStatus());
    }

    public function testRevoke()
    {
        $this->_subscription->revoke();
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame(\Magento\PubSub\SubscriptionInterface::STATUS_REVOKED, $this->_subscription->getStatus());
    }

    public function testAlias()
    {
        $this->assertNull($this->_subscription->getAlias());
        $this->assertSame($this->_subscription, $this->_subscription->setAlias('some alias'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some alias', $this->_subscription->getAlias());
    }

    public function testTopics()
    {
        $this->assertNull($this->_subscription->getTopics());
        $this->assertSame($this->_subscription, $this->_subscription->setTopics(array('topic_one', 'topic_two')));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertTrue($this->_subscription->hasTopic('topic_one'));
        $this->assertTrue($this->_subscription->hasTopic('topic_two'));
        $this->assertFalse($this->_subscription->hasTopic('topic_three'));
        $this->assertSame(array('topic_one', 'topic_two'), $this->_subscription->getTopics());
    }

    public function testRegistrationMechanism()
    {
        $this->assertFalse($this->_subscription->hasRegistrationMechanism());
        $this->assertNull($this->_subscription->getRegistrationMechanism());
        $this->assertSame(
            $this->_subscription,
            $this->_subscription->setRegistrationMechanism(
                \Magento\Webhook\Model\Subscription::REGISTRATION_MECHANISM_MANUAL
            )
        );
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertTrue($this->_subscription->hasRegistrationMechanism());
        $this->assertSame(
            \Magento\Webhook\Model\Subscription::REGISTRATION_MECHANISM_MANUAL,
            $this->_subscription->getRegistrationMechanism()
        );
    }

    public function testEndpointUrl()
    {
        $this->assertNull($this->_subscription->getEndpointUrl());
        $this->assertSame($this->_subscription, $this->_subscription->setEndpointUrl('some endpoint url'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some endpoint url', $this->_subscription->getEndpointUrl());
    }

    public function testTimeOutInSeconds()
    {
        $this->assertNull($this->_subscription->getTimeoutInSecs());
        $this->assertSame($this->_subscription, $this->_subscription->setTimeoutInSecs('some timeout in secs'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some timeout in secs', $this->_subscription->getTimeoutInSecs());
    }

    public function testFormat()
    {
        $this->assertNull($this->_subscription->getFormat());
        $this->assertSame($this->_subscription, $this->_subscription->setFormat('some format'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some format', $this->_subscription->getFormat());
    }

    public function testApiUserId()
    {
        $this->assertNull($this->_subscription->getApiUserId());
        $this->assertSame($this->_subscription, $this->_subscription->setApiUserId('some api user id'));
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some api user id', $this->_subscription->getApiUserId());
    }

    public function testUser()
    {
        $this->assertSame($this->_mockUser, $this->_subscription->getUser());
    }

    public function testAuthenticationType()
    {
        $this->assertNull($this->_subscription->getAuthenticationType());
        $this->assertSame(
            $this->_subscription, $this->_subscription->setAuthenticationType('some authentication type')
        );
        $this->assertTrue($this->_subscription->hasDataChanges());
        $this->assertSame('some authentication type', $this->_subscription->getAuthenticationType());
    }
}
