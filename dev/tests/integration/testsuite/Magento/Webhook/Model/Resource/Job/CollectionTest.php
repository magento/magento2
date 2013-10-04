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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Resource\Job;

/**
 * \Magento\Webhook\Model\Resource\Job\Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webhook\Model\Subscription */
    protected $_subscription;

    /** @var \Magento\Webhook\Model\Event */
    protected $_event;

    /** @var \Magento\Webhook\Model\Endpoint */
    protected $_endpoint;

    /** @var \Magento\Webapi\Model\Acl\User */
    protected $_user;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_user = $this->_objectManager->create('Magento\Webapi\Model\Acl\User')
            ->setApiKey(md5(rand(0, time())))
            ->save();
        $this->_endpoint = $this->_objectManager->create('Magento\Webhook\Model\Endpoint')
            ->setEndpointUrl('test')
            ->setTimeoutInSecs('test')
            ->setFormat('test')
            ->setAuthenticationType('authentication_type');
        $this->_subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription',
            array('endpoint' => $this->_endpoint))
            ->setApiUserId($this->_user->getId())
            ->save();
        $this->_event = $this->_objectManager->create('Magento\Webhook\Model\Event')
            ->save();
    }

    protected function tearDown()
    {
        $this->_subscription->delete();
        $this->_event->delete();
        $this->_endpoint->delete();
        $this->_user->delete();
    }

    public function testInit()
    {
        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');
        $this->assertEquals('Magento\Webhook\Model\Resource\Job', $collection->getResourceModelName());
        $this->assertEquals('Magento\Webhook\Model\Job', $collection->getModelName());
    }

    public function testNewEventInNewCollection()
    {
        $job1 = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->setSubscriptionId($this->_subscription->getId())
            ->setEventId($this->_event->getId())
            ->save();

        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');
        $this->assertEquals(1, count($collection->getItems()));
        $this->assertEquals($job1->getId(), $collection->getFirstItem()->getId());

        $job2 = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->setSubscriptionId($this->_subscription->getId())
            ->setEventId($this->_event->getId())
            ->save();

        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collectionSecond */
        $collectionSecond = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');
        $this->assertEquals(1, count($collectionSecond->getItems()));
        $this->assertEquals($job2->getId(), $collectionSecond->getFirstItem()->getId(),
            sprintf("Event #%s is expected in second collection,"
                . "found event #%s. It could lead to race conditions issue if it is #%s",
                $job2->getId(), $collectionSecond->getFirstItem()->getId(), $job1->getId())
        );

        $job1->delete();
        $job2->delete();
    }

    /**
     * Emulates concurrent transactions. Executes 50 seconds because of lock timeout
     *
     * @expectedException \Zend_Db_Statement_Exception
     * @expectedMessage SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction
     */
    public function testParallelTransactions()
    {
        $job = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->setSubscriptionId($this->_subscription->getId())
            ->setEventId($this->_event->getId())
            ->save();
        $job2 = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->setSubscriptionId($this->_subscription->getId())
            ->setEventId($this->_event->getId())
            ->save();
        $job3 = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->setSubscriptionId($this->_subscription->getId())
            ->setEventId($this->_event->getId())
            ->setStatus(\Magento\PubSub\JobInterface::STATUS_IN_PROGRESS)
            ->save();

        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');

        $beforeLoad = new \ReflectionMethod(
            'Magento\Webhook\Model\Resource\Job\Collection', '_beforeLoad');
        $beforeLoad->setAccessible(true);
        $beforeLoad->invoke($collection);
        $data = $collection->getData();
        $this->assertEquals(2, count($data));

        /** @var \Magento\Core\Model\Resource $resource */
        $resource = $this->_objectManager->create('Magento\Core\Model\Resource');
        $connection = $resource->getConnection('core_write');

        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collection2 */
        $collection2 = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');
        $collection2->setConnection($connection);
        $initSelect = new \ReflectionMethod(
            'Magento\Webhook\Model\Resource\Job\Collection', '_initSelect');
        $initSelect->setAccessible(true);
        $initSelect->invoke($collection2);


        $afterLoad = new \ReflectionMethod(
            'Magento\Webhook\Model\Resource\Job\Collection', '_afterLoad');
        $afterLoad->setAccessible(true);


        try {
            $collection2->getData();
        } catch (\Zend_Db_Statement_Exception $e) {
            $job->delete();
            $job2->delete();
            $job3->delete();
            $afterLoad->invoke($collection);

            throw ($e);
        }
        $job->delete();
        $job2->delete();
        $job3->delete();
        $afterLoad->invoke($collection);
    }

    public function testRevokeIdlingInProgress()
    {
        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertNull($collection->revokeIdlingInProgress());
    }
}
