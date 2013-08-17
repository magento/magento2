<?php
/**
 * Mage_Webhook_Model_Event_Factory
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Event_FactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webhook_Model_Event_Factory */
    protected $_factory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManager;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_arrayConverter;

    public function setUp()
    {
        $this->_objectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_arrayConverter = $this->getMockBuilder('Varien_Convert_Object')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_factory = new Mage_Webhook_Model_Event_Factory($this->_objectManager, $this->_arrayConverter);
    }

    public function testCreate()
    {
        $webhookEvent = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->disableOriginalConstructor()
            ->getMock();
        $topic = 'TEST_TOPIC';
        $data = 'TEST_DATA';
        $array = 'TEST_ARRAY';
        $this->_arrayConverter->expects($this->once())
            ->method('convertDataToArray')
            ->with($this->equalTo($data))
            ->will($this->returnValue($array));
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('Mage_Webhook_Model_Event'),
                $this->equalTo(
                    array(
                         'data' => array(
                             'topic'     => $topic,
                             'body_data' => serialize($array),
                             'status'    => Magento_PubSub_EventInterface::READY_TO_SEND
                         )
                    )
                )
            )
            ->will($this->returnValue($webhookEvent));
        $webhookEvent->expects($this->once())
            ->method('setDataChanges')
            ->with($this->equalTo(true))
            ->will($this->returnSelf());
        $this->assertSame($webhookEvent, $this->_factory->create($topic, $data));
    }

    public function testCreateEmpty()
    {
        $testValue = "test value";
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Mage_Webhook_Model_Event'))
            ->will($this->returnValue($testValue));
        $this->assertSame($testValue, $this->_factory->createEmpty());
    }
}
