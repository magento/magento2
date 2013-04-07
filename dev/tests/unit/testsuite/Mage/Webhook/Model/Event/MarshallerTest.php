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
class Mage_Webhook_Model_Event_MarshallerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_topic;

    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_mappingsConfig;

    /**
     * @var Mage_Core_Model_Event_Queue
     */
    protected $_queueMock;

    /**
     * @var Mage_Webhook_Model_Mapper_Factory
     */
    protected $_mapperFactoryMock;

    /**
     * @var Mage_Webhook_Model_Mapper_Factory_Interface
     */
    protected $_actualMapperFactory;

    /**
     * @var Mage_Webhook_Model_Resource_Subscriber_Collection
     */
    protected $_subscriberColMock;

    protected $_eventFactoryMock;

    /**
     * @var Mage_Webhook_Model_Event_Marshaller
     */
    protected $_mockObject;

    public function setUp()
    {
        parent::setUp();

        $this->_topic = "randomTopic";

        $this->_mappingsConfig = new Mage_Core_Model_Config_Element('<mappings>
            <testMapping>
                <options>
                </options>
                <mapper_factory>Stub_Mapper_Factory_Default</mapper_factory>
            </testMapping>
            <testMapping2>
                <options>
                </options>
                <mapper_factory>Stub_Mapper_Factory_Default</mapper_factory>
            </testMapping2>
        </mappings>');

        $this->_queueMock = $this->getMockBuilder('Mage_Webhook_Model_Event_Queue')->disableOriginalConstructor()
                ->setMethods(array('offer'))->getMock();

        $this->_eventFactoryMock = $this->getMockBuilder('Mage_Webhook_Model_Event_Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('createEvent'))->getMock();


        $this->_mapperFactoryMock = $this->getMockBuilder('Mage_Webhook_Model_Mapper_Factory')
                                            ->disableOriginalConstructor()
                                            ->setMethods(array('getMapperFactory'))
                                            ->getMock();

        $this->_subscriberColMock =
                $this->getMockBuilder('Mage_Webhook_Model_Resource_Subscriber_Collection')->disableOriginalConstructor()
                        ->getMock();

        $this->_subscriberColMock->expects($this->once())->method('addTopicFilter')->with($this->_topic)
                ->will($this->returnSelf());
        $this->_subscriberColMock->expects($this->once())->method('addIsActiveFilter')->with(true)
                ->will($this->returnSelf());

        $this->_mockObject = $this->getMock('Mage_Webhook_Model_Event_Marshaller',
                                            array('_getSubscriberCollection'),
                                            array($this->_mappingsConfig,
                                                  $this->_queueMock,
                                                  $this->_mapperFactoryMock,
                                                  $this->_eventFactoryMock
                                            ));

        $this->_mockObject->expects($this->once())
                ->method('_getSubscriberCollection')
                ->will($this->returnValue($this->_subscriberColMock));
    }

    /**
     * Tests when there are no subscribers for the topic.
     * Expects no events to be created.
     */
    public function testMarshallNoSubscribers()
    {
        $data = array('hello' => 'world');

        $subscribers = array();
        $this->_subscriberColMock->expects($this->once())
                ->method('getItems')
                ->will($this->returnValue($subscribers));

        $this->assertEquals(true, $this->_mockObject->marshal($this->_topic, $data));
    }

    /**
     * Tests when there is one subscriber for the topic with one mapping.
     * Expects one event to be created.
     */
    public function testMarshallOneSubscriber()
    {
        $data = array('hello' => 'world');
        $mapping = 'testMapping';
        $headers = array('h1' => 'h2');

        $this->_subscriberCollectionExpect(array($mapping));
        $this->_createMappers(array($mapping), $data, $headers);

        $event1 = $this->_createEventMock($mapping, $data, $headers);

        $this->_eventFactoryMock->expects($this->once())
            ->method('createEvent')
            ->will($this->onConsecutiveCalls($event1));

        $this->_queueExpect(array($event1));

        $output = $this->_mockObject->marshal($this->_topic, $data);

        $this->assertEquals(true, $output);
    }

    /**
     * Tests when there are two subscribers for the topic with one mapping.
     * Expects one event to be created.
     */
    public function testMarshallManySubscribersSameMapping()
    {
        $data = array('hello' => 'world');
        $mapping = 'testMapping';
        $headers = array('h1' => 'h2');

        $this->_subscriberCollectionExpect(array($mapping, $mapping));
        $this->_createMappers(array($mapping), $data, $headers);

        $event1 = $this->_createEventMock($mapping, $data, $headers);

        $this->_eventFactoryMock->expects($this->once())
            ->method('createEvent')
            ->will($this->onConsecutiveCalls($event1));

        $this->_queueExpect(array($event1));

        $output = $this->_mockObject->marshal($this->_topic, $data);

        $this->assertEquals(true, $output);
    }

    /**
     * Tests when there are two subscribers for the topic with two different mapping.
     * Expects two event to be created.
     */
    public function testMarshallManySubscribersDifferentMappings()
    {
        $data = array('hello' => 'world');
        $mapping = 'testMapping';
        $mapping2 = 'testMapping2';

        $headers = array('h1' => 'h2');

        $this->_subscriberCollectionExpect(array($mapping, $mapping2));
        $this->_createMappers(array($mapping, $mapping2), $data, $headers);

        $event1 = $this->_createEventMock($mapping, $data, $headers);
        $event2 = $this->_createEventMock($mapping2, $data, $headers);

        $this->_eventFactoryMock->expects($this->exactly(2))
            ->method('createEvent')
            ->will($this->onConsecutiveCalls($event1, $event2));

        $this->_queueExpect(array($event1, $event2));

        $output = $this->_mockObject->marshal($this->_topic, $data);

        $this->assertEquals(true, $output);
    }

    /**
     * Tests when there are 3 subscribers for the topic with two different mappings.
     * Expects two event to be created.
     */
    public function testMarshallManySubscribersDifferentMappingsSomeSameMapping()
    {
        $data = array('hello' => 'world');
        $mapping = 'testMapping';
        $mapping2 = 'testMapping2';

        $headers = array('h1' => 'h2');

        $this->_subscriberCollectionExpect(array($mapping, $mapping2, $mapping));
        $this->_createMappers(array($mapping, $mapping2), $data, $headers);

        $event1 = $this->_createEventMock($mapping, $data, $headers);
        $event2 = $this->_createEventMock($mapping2, $data, $headers);

        $this->_eventFactoryMock->expects($this->exactly(2))
            ->method('createEvent')
            ->will($this->onConsecutiveCalls($event1, $event2));

        $this->_queueExpect(array($event1, $event2));

        $output = $this->_mockObject->marshal($this->_topic, $data);

        $this->assertEquals(true, $output);
    }

    protected function _subscriberCollectionExpect(array $subscriberMappings)
    {
        $subscribers = array();

        foreach ($subscriberMappings as $subMapping) {
            $subscribers[] = $this->_createSubscriber($subMapping);
        }

        $this->_subscriberColMock->expects($this->once())
                ->method('getItems')
                ->will($this->returnValue($subscribers));
    }

    protected function _createMappers(array $mappings, $data, $headers)
    {
        $counter = 0;
        foreach ($mappings as $mapping) {
            $actualMapperFactory = $this->getMockBuilder('Mage_Webhook_Model_Mapper_Factory_Interface')
                                          ->disableOriginalConstructor()
                                          ->setMethods(array('getMapper'))
                                          ->getMock();

            $this->_mapperFactoryMock->expects($this->at($counter))
                                        ->method('getMapperFactory')
                                        ->with($mapping, $this->_mappingsConfig)
                                        ->will($this->returnValue($actualMapperFactory));

            $actualMapper = $this->_createMapper($data, $headers);

            $actualMapperFactory->expects($this->once())
                                   ->method('getMapper')
                                   ->with($this->_topic, $data, $this->anything())
                                   ->will($this->returnValue($actualMapper));
            $counter++;
        }
    }

    protected function _queueExpect(array $events)
    {
        $counter = 0;
        foreach ($events as $event) {
            $this->_queueMock->expects($this->at($counter))
                    ->method('offer')
                    ->with($event)
                    ->will($this->returnValue(true));
            $counter++;
        }
    }

    protected function _createSubscriber($mapping)
    {
        $sub = $this->getMockBuilder('Mage_Webhook_Model_Subscriber')
                                         ->disableOriginalConstructor()
                                         ->setMethods(array('getMapping'))
                                         ->getMock();
        $sub->expects($this->once())->method('getMapping')->will($this->returnValue($mapping));

        return $sub;
    }

    protected function _createMapper($bodyData, $headers)
    {
        $actualMapper = $this->getMockBuilder('Mage_Webhook_Model_Mapper_Interface')
                ->disableOriginalConstructor()
                ->setMethods(array('getTopic', 'getData', 'getHeaders'))
                ->getMock();

        $actualMapper->expects($this->once())
                ->method('getTopic')
                ->will($this->returnValue($this->_topic));

        $actualMapper->expects($this->once())
                ->method('getData')
                ->will($this->returnValue($bodyData));

        $actualMapper->expects($this->once())
                ->method('getHeaders')
                ->will($this->returnValue($headers));

        return $actualMapper;
    }

    protected function _createEventMock($mapping, $bodyData, $headers)
    {
        $eventMock = $this->getMockBuilder('Mage_Webhook_Model_Event')
                          ->disableOriginalConstructor()
                          ->setMethods(array('setTopic', 'setBodyData', 'setMapping', 'setHeaders'))
                          ->getMock();
        $eventMock->expects($this->once())
                ->method('setTopic')
                ->with($this->_topic)
                ->will($this->returnSelf());
        $eventMock->expects($this->once())
                ->method('setBodyData')
                ->with($bodyData)
                ->will($this->returnSelf());
        $eventMock->expects($this->once())
                  ->method('setMapping')
                  ->with($mapping)
                  ->will($this->returnSelf());
        $eventMock->expects($this->once())
                ->method('setHeaders')
                ->with($headers)
                ->will($this->returnSelf());

        return $eventMock;
    }
}
