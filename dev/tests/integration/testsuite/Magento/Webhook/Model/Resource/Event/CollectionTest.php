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
namespace Magento\Webhook\Model\Resource\Event;

/**
 * \Magento\Webhook\Model\Resource\Event\Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testInit()
    {
        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertEquals('Magento\Webhook\Model\Resource\Event', $collection->getResourceModelName());
        $this->assertEquals('Magento\Webhook\Model\Event', $collection->getModelName());

        /* check FOR UPDATE lock */
        $forUpdate = $collection->getSelect()->getPart(\Zend_Db_Select::FOR_UPDATE);
        $this->assertTrue($forUpdate);

        $where = array("(`status` = '" . \Magento\PubSub\EventInterface::STATUS_READY_TO_SEND . "')");
        $this->assertEquals($where, $collection->getSelect()->getPart(\Zend_Db_Select::WHERE));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetData()
    {
        $event = $this->_objectManager->create('Magento\Webhook\Model\Event')->save();

        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertEquals(1, count($collection->getItems()));

        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collectionSecond */
        $collectionSecond = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertEquals(0, count($collectionSecond->getItems()));

        $updatedEvent = $this->_objectManager->create('Magento\Webhook\Model\Event')
            ->load($event->getId());

        $this->assertEquals(\Magento\PubSub\EventInterface::STATUS_IN_PROGRESS, $updatedEvent->getStatus());
        $event->delete();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testNewEventInNewCollection()
    {
        $event1 = $this->_objectManager->create('Magento\Webhook\Model\Event')->save();

        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertEquals(1, count($collection->getItems()));
        $this->assertEquals($event1->getId(), $collection->getFirstItem()->getId());

        $event2 = $this->_objectManager->create('Magento\Webhook\Model\Event')->save();

        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collectionSecond */
        $collectionSecond = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertEquals(1, count($collectionSecond->getItems()));
        $this->assertEquals($event2->getId(), $collectionSecond->getFirstItem()->getId(),
            sprintf("Event #%s is expected in second collection,"
                    . "found event #%s. It could lead to race conditions issue if it is #%s",
            $event2->getId(), $collectionSecond->getFirstItem()->getId(), $event1->getId())
        );

        $event1->delete();
        $event2->delete();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRevokeIdlingInProgress()
    {
        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $this->assertNull($collection->revokeIdlingInProgress());
    }

    /**
     * Emulates concurrent transactions. Executes 50 seconds because of lock timeout
     *
     * @magentoDbIsolation enabled
     * @expectedException \Zend_Db_Statement_Exception
     * @expectedMessage SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction
     */
    public function testParallelTransactions()
    {
        $event = $this->_objectManager->create('Magento\Webhook\Model\Event')->save();
        $event2 = $this->_objectManager->create('Magento\Webhook\Model\Event')->save();
        /** @var \Magento\Webhook\Model\Event $event3 */
        $event3 = $this->_objectManager->create('Magento\Webhook\Model\Event')
            ->setStatus(\Magento\PubSub\EventInterface::STATUS_IN_PROGRESS)
            ->save();

        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');



        $beforeLoad = new \ReflectionMethod(
            'Magento\Webhook\Model\Resource\Event\Collection', '_beforeLoad');
        $beforeLoad->setAccessible(true);
        $beforeLoad->invoke($collection);
        $data = $collection->getData();
        $this->assertEquals(2, count($data));

        /** @var \Magento\Core\Model\Resource $resource */
        $resource = $this->_objectManager->create('Magento\Core\Model\Resource');
        $connection = $resource->getConnection('core_write');

        /** @var \Magento\Webhook\Model\Resource\Event\Collection $collection2 */
        $collection2 = $this->_objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $collection2->setConnection($connection);
        $initSelect = new \ReflectionMethod(
            'Magento\Webhook\Model\Resource\Event\Collection', '_initSelect');
        $initSelect->setAccessible(true);
        $initSelect->invoke($collection2);


        $afterLoad = new \ReflectionMethod(
            'Magento\Webhook\Model\Resource\Event\Collection', '_afterLoad');
        $afterLoad->setAccessible(true);


        try {
            $collection2->getData();
        } catch (\Zend_Db_Statement_Exception $e) {
            $event->delete();
            $event2->delete();
            $event3->delete();
            $afterLoad->invoke($collection);

            throw ($e);
        }
        $event->delete();
        $event2->delete();
        $event3->delete();
        $afterLoad->invoke($collection);
    }
}
