<?php
/**
 * \Magento\Webhook\Model\Resource\Subscription
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
namespace Magento\Webhook\Model\Resource;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    const MAIN_TABLE_NAME = 'webhook_subscription_table';
    const HOOK_TABLE_NAME = 'webhook_subscription_hook_table';
    const SUBSCRIPTION_ID = '1';

    /**
     * Contents of dummy config element
     */
    const TOPICS_XML =
                '<test>
                    <a>
                        <label>label</label>
                    </a>
                    <c>
                        <label>label</label>
                    </c>
                </test>';
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_selectMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_adapterMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_resourceMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_configMock;

    /**
     * Unit under testing.
     *
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $_subxResourceMock;

    protected function setUp()
    {
        $this->_selectMock = $this->_makeMock('Magento\DB\Select');
        $this->_resourceMock = $this->_makeMock('Magento\Core\Model\Resource');
        $this->_adapterMock = $this->_makeMock('Magento\DB\Adapter\Pdo\Mysql');
        $this->_adapterMock->expects($this->any())
            ->method('select')
            ->with()
            ->will($this->returnValue($this->_selectMock));

        // Config mock
        $configMethods = array('getNode', 'setNode', 'getXpath', 'reinit');
        $this->_configMock = $this->getMock('Magento\Core\Model\ConfigInterface', $configMethods, array(), '', false);
    }

    /**
     * Tests methods which can only be executed by calling the parent's save method.
     *
     * Includes _afterSave, _updateTopics, _getSupportedTopics, and _performTopicUpdates
     */
    public function testMethodsUnderSave()
    {
        $data = array('subscription_id' => null);

        // Subscription resource
        $methods = array('_getWriteAdapter', '_getReadAdapter', '_prepareDataForSave', 'getMainTable', 'getTable');

        $this->_subxResourceMock = $this->_makeSubscriptionResourceMock($methods);
        $this->_subxResourceMock->expects($this->any())
            ->method('_prepareDataForSave')
            ->will($this->returnValue($data));
        $this->_subxResourceMock->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('webhook_subscription_table'));
        $this->_subxResourceMock->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue(self::HOOK_TABLE_NAME));
        $this->_subxResourceMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($this->_adapterMock));
        $this->_subxResourceMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($this->_adapterMock));

        // Select stubs
        $this->_selectMock->expects($this->once())
            ->method('from')
            ->with(self::HOOK_TABLE_NAME, 'topic')
            ->will($this->returnSelf());
        $this->_selectMock->expects($this->once())
            ->method('where')
            ->with('subscription_id = ?', self::SUBSCRIPTION_ID)
            ->will($this->returnSelf());

        // Subscription model mock
        $subscriptionMock = $this->_makeMock('Magento\Webhook\Model\Subscription');
        $subscriptionMock->expects($this->any())
            ->method('getId')
            ->with()
            ->will($this->returnValue(self::SUBSCRIPTION_ID));

        // Tests logic to update and save topics
        $newTopics = array('a'=>'a', 'b'=>'b');
        $oldTopics = array('c'=>'c');

        $this->_adapterMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->_selectMock)
            ->will($this->returnValue( $oldTopics ));
        $subscriptionMock->expects($this->once())
            ->method('getData')
            ->with('topics')
            ->will($this->returnValue($newTopics));
        $configElement = new \Magento\Core\Model\Config\Element(self::TOPICS_XML);
        $this->_configMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($configElement));

        // Adapter stubs
        $this->_adapterMock->expects($this->once())
            ->method('delete')
            ->with(self::HOOK_TABLE_NAME, array(
                'subscription_id = ?' => self::SUBSCRIPTION_ID,
                'topic in (?)' => array('c' => 'c')
            ));
        $this->_adapterMock->expects($this->once())
            ->method('insertMultiple')
            ->with(self::HOOK_TABLE_NAME, array(
                array('subscription_id' => self::SUBSCRIPTION_ID, 'topic' => 'a')
            ));

        $this->_subxResourceMock->save($subscriptionMock);
    }

    /**
     * Tests _afterLoad and loadTopics
     */
    public function testMethodsUnderLoad()
    {
        // Subscription resource mock
        $methods = array('_getWriteAdapter', '_getReadAdapter', '_prepareDataForSave', 'getMainTable', 'getTable');

        $this->_subxResourceMock = $this->_makeSubscriptionResourceMock($methods);
        $this->_subxResourceMock->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue(self::MAIN_TABLE_NAME));
        $this->_subxResourceMock->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue(self::HOOK_TABLE_NAME));
        $this->_subxResourceMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($this->_adapterMock));

        // Select stubs
        $this->_selectMock->expects($this->exactly(2))
            ->method('from')
            ->will($this->returnSelf());
        $this->_selectMock->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf());

        // Subscription model mock
        $subscriptionMock = $this->_makeMock('Magento\Webhook\Model\Subscription');
        $subscriptionMock->expects($this->any())
            ->method('getId')
            ->with()
            ->will($this->returnValue(self::SUBSCRIPTION_ID));

        $this->_subxResourceMock->load($subscriptionMock, self::SUBSCRIPTION_ID);
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
     * Generates a mock subscription resource with the given methods stubbed
     *
     * @param $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _makeSubscriptionResourceMock($methods)
    {
        return $this->getMock(
            'Magento\Webhook\Model\Resource\Subscription',
            $methods,
            array($this->_resourceMock, $this->_configMock),
            '',
            true
        );
    }
}
