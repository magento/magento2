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
class Mage_Webhook_Model_Event_FactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webhook_Model_Event_Factory */
    protected $_object;

    /** @var Magento_ObjectManager */
    protected $_objectManager;

    public function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager');

        $this->_object = new Mage_Webhook_Model_Event_Factory($this->_objectManager);
    }

    /**
     * @dataProvider eventDataDatasource
     */
    public function testCreateCallbackEvent($eventData)
    {
        $classMock = $this->setupObjectManager('Mage_Webhook_Model_Event_Callback', $eventData);
        $event = $this->_object->createEvent(Mage_Webhook_Model_Event_Interface::EVENT_TYPE_CALLBACK, $eventData);
        $this->assertEquals($classMock, $event);
    }
    /**
     * @dataProvider eventDataDatasource
     */
    public function testCreateInformEvent($eventData)
    {
        $classMock = $this->setupObjectManager('Mage_Webhook_Model_Event', $eventData);
        $event = $this->_object->createEvent(Mage_Webhook_Model_Event_Interface::EVENT_TYPE_INFORM, $eventData);
        $this->assertEquals($classMock, $event);
    }

    public function setupObjectManager($className, $data)
    {
        $classMock = $this->getMock($className, array(), array(), '', false);
        $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo($className), $this->equalTo($data))
            ->will($this->returnValue($classMock));
        return $classMock;
    }

    public function eventDataDatasource()
    {
        return array(
            array( array(
                'mapping' => array(),
                'bodyData'  => array(),
                'headers'   => array(),
                'topic'     => 'some/topic',
                'status'    => Mage_Webhook_Model_Event::READY_TO_SEND
            )),
            array( array(
                'mapping' => array(),
                'bodyData'  => array(),
                'headers'   => array(),
                'topic'     => 'some/topic'
            ))
        );
    }
}
