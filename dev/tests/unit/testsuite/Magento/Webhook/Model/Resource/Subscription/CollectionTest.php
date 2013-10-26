<?php
/**
 * \Magento\Webhook\Model\Resource\Subscription\Collection
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
namespace Magento\Webhook\Model\Resource\Subscription;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Arguments passed to methods under testing
     */
    const TOPIC = 'customer/topic';
    const ALIAS = 'some_alias';
    const API_USER_ID = 'api_user id';

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $_connectionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $_selectMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $_endpointResMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $_fetchStrategyMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $_resourceMock;

    /** @var  \Magento\Core\Model\EntityFactory */
    private $_entityFactory;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @var \Magento\Core\Model\Logger
     */
    private $_loggerMock;

    protected function setUp()
    {
        $this->_loggerMock = $this->getMock('Magento\Core\Model\Logger', array(), array(), '', false);
        $this->_selectMock = $this->_makeMock('Zend_Db_Select');
        $this->_selectMock->expects($this->any())
            ->method('from')
            ->with(array('main_table' => null));
        $this->_connectionMock = $this->_makeMock('Magento\DB\Adapter\Pdo\Mysql');

        $this->_connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->_selectMock));

        $subscriptionMock = $this->_makeMock('Magento\Webhook\Model\Subscription');
        $eventMgrMock = $this->_makeMock('Magento\Event\ManagerInterface');

        // Arguments to collection constructor
        $this->_eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $this->_fetchStrategyMock = $this->_makeMock('Magento\Data\Collection\Db\FetchStrategyInterface');
        $this->_endpointResMock = $this->_makeMock('Magento\Webhook\Model\Resource\Endpoint');
        $this->_resourceMock = $this-> _makeMock('Magento\Webhook\Model\Resource\Subscription');
        $this->_resourceMock->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($this->_connectionMock));
        $this->_entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        // Mock object manager
        $createReturnMap = array(
            array('Magento\Webhook\Model\Resource\Subscription', array(), $this->_resourceMock),
            array('Magento\Webhook\Model\Subscription', array(), $subscriptionMock)
        );
        $getReturnMap = array(
            array('Magento\Event\ManagerInterface', $eventMgrMock),
        );
        $mockObjectManager = $this->_setMageObjectManager();
        $mockObjectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap($createReturnMap));
        $mockObjectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($getReturnMap));
    }

    public function testInitialization()
    {
        $collection = $this->_makeCollectionMock(array('load')); // At least one method has to be specified
        $this->assertEquals('Magento\Webhook\Model\Subscription', $collection->getModelName());
        $this->assertEquals('Magento\Webhook\Model\Resource\Subscription', $collection->getResourceModelName());
    }

    public function testGetSubscriptionsByTopic()
    {
        $subscriptions = array('subscription1', 'subscription2', 'subscription3');
        $methods = array('getItems', 'addTopicFilter');
        $collection = $this->_makeCollectionMock($methods);

        $collection->expects($this->once())
            ->method('getItems')
            ->with()
            ->will($this->returnValue($subscriptions));
        $collection->expects($this->once())
            ->method('addTopicFilter')
            ->with(self::TOPIC)
            ->will($this->returnSelf());

        $this->assertEquals($subscriptions, $collection->getSubscriptionsByTopic(self::TOPIC));
    }

    public function testGetSubscriptionsByAlias()
    {
        $subscriptions = array('subscription1', 'subscription2', 'subscription3');
        $methods = array('getItems', 'addAliasFilter');
        $collection = $this->_makeCollectionMock($methods);

        $collection->expects($this->once())
            ->method('getItems')
            ->with()
            ->will($this->returnValue($subscriptions));
        $collection->expects($this->once())
            ->method('addAliasFilter')
            ->with(self::ALIAS)
            ->will($this->returnSelf());

        $this->assertEquals($subscriptions, $collection->getSubscriptionsByAlias(self::ALIAS));
    }

    public function testGetActivatedSubscriptionsWithoutApiUser()
    {
        $methods = array('addEndpointIdsFilter', 'getItems');
        $subscriptions = array('subscription1', 'subscription2', 'subscription3');
        $endpointIds = array('endpoint_id_1','endpoint_id_2','endpoint_id_3');

        $this->_endpointResMock->expects($this->once())
            ->method('getEndpointsWithoutApiUser')
            ->will($this->returnValue($endpointIds));

        $collection = $this->_makeCollectionMock($methods);
        $collection->expects($this->once())
            ->method('addEndpointIdsFilter')
            ->with($endpointIds)
            ->will($this->returnSelf());
        $collection->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($subscriptions));

        $this->assertEquals($subscriptions, $collection->getActivatedSubscriptionsWithoutApiUser());
    }

    public function testGetApiUserSubscriptions()
    {
        $methods = array('addEndpointIdsFilter', 'getItems');
        $subscriptions = array('subscription1', 'subscription2', 'subscription3');
        $endpointIds = array('endpoint_id_1','endpoint_id_2','endpoint_id_3');

        $this->_endpointResMock->expects($this->once())
            ->method('getApiUserEndpoints')
            ->with(self::API_USER_ID)
            ->will($this->returnValue($endpointIds));

        $collection = $this->_makeCollectionMock($methods);
        $collection->expects($this->once())
            ->method('addEndpointIdsFilter')
            ->with($endpointIds)
            ->will($this->returnSelf());
        $collection->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($subscriptions));

        $this->assertEquals($subscriptions, $collection->getApiUserSubscriptions(self::API_USER_ID));
    }

    public function testClearFilters()
    {
        $collection = $this->_makeCollectionMock(array('load'));
        // Cannot test number of calls because other tests use this member
        $this->_selectMock->expects($this->any())
            ->method('from')
            ->with(array('main_table' => null));
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection', $collection->clearFilters());
    }

    public function testAddEndpointIdsFilter()
    {
        $collection = $this->_makeCollectionMock(array('load'));
        $endpointIds = array('endpoint_id_1','endpoint_id_2','endpoint_id_3');
        $this->_selectMock->expects($this->once())
            ->method('where')
            ->with('endpoint_id IN (?)', $endpointIds);
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection',
            $collection->addEndpointIdsFilter($endpointIds));
    }

    public function testAddTopicFilter()
    {
        $this->_connectionMock->expects($this->once())
            ->method('quoteInto')
            ->with('hooks.subscription_id=main_table.subscription_id AND hooks.topic=?', self::TOPIC);
        $collection = $this->_makeCollectionMock(array('load'));
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection',
            $collection->addTopicFilter(self::TOPIC));
    }

    public function testAddAliasFilter()
    {
        $collection = $this->_makeCollectionMock(array('addFieldToFilter'));
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('alias', self::ALIAS);

        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection',
            $collection->addAliasFilter(self::ALIAS));
    }

    public function testAddIsActiveFilter()
    {
        $collection = $this->_makeCollectionMock(array('addFieldToFilter'));
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('status', \Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE);
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection',
            $collection->addIsActiveFilter(true));
    }

    public function testAddIsActiveFilterNotActive()
    {
        $collection = $this->_makeCollectionMock(array('addFieldToFilter'));
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('status', \Magento\PubSub\SubscriptionInterface::STATUS_INACTIVE);
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection',
            $collection->addIsActiveFilter(false));
    }

    public function testAddNotInactiveFilter()
    {
        $collection = $this->_makeCollectionMock(array('load'));
        $this->_selectMock->expects($this->any())
            ->method('where')
            ->with('status IN (?)', array(
                \Magento\Webhook\Model\Subscription::STATUS_ACTIVE,
                \Magento\Webhook\Model\Subscription::STATUS_REVOKED));
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Subscription\Collection',
            $collection->addNotInactiveFilter());
    }

    /**
     * Generations a collection mock, with the given methods stubbed
     *
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _makeCollectionMock(array $methods)
    {
        return $this->getMock('Magento\Webhook\Model\Resource\Subscription\Collection', $methods,
            array(
                $this->_endpointResMock,
                $this->_eventManager,
                $this->_loggerMock,
                $this->_fetchStrategyMock,
                $this->_entityFactory,
                $this->_resourceMock
            ), '', true);
    }

    /**
     * Generates a mock object of the given class
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _makeMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Makes sure that Mage has a mock object manager set, and returns that instance.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _setMageObjectManager()
    {
        $mockObjectManager = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        \Magento\Core\Model\ObjectManager::setInstance($mockObjectManager);

        return $mockObjectManager;
    }
}
