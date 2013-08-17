<?php
/**
 * Mage_Webhook_Model_Resource_Job_Collection
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
class Mage_Webhook_Model_Resource_Job_CollectionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $mockDBAdapter = $this->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mockResourceEvent = $this->getMockBuilder('Mage_Webhook_Model_Resource_Job')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResourceEvent->expects($this->once())
            ->method('getReadConnection')
            ->will($this->returnValue($mockDBAdapter));

        $mockObjectManager = $this->_setMageObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Mage_Webhook_Model_Resource_Job'))
            ->will($this->returnValue($mockResourceEvent));
    }

    public function tearDown()
    {
        // Unsets object manager
        Mage::reset();
    }

    public function testConstructor()
    {
        $mockFetchStrategy = $this->getMockBuilder('Varien_Data_Collection_Db_FetchStrategyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $collection = new Mage_Webhook_Model_Resource_Job_Collection($mockFetchStrategy);
        $this->assertInstanceOf('Mage_Webhook_Model_Resource_Job_Collection', $collection);
        $this->assertEquals('Mage_Webhook_Model_Resource_Job', $collection->getResourceModelName());
    }

    public function testSetPageLimit()
    {
        $mockFetchStrategy = $this->getMockBuilder('Varien_Data_Collection_Db_FetchStrategyInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMockBuilder('Zend_Db_Select')
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->once())
            ->method('limitPage');

        $connMock = $this->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        // this method is simply used to set a value, it is not being tested
        $connMock->expects($this->any())
            ->method('select')
            ->withAnyParameters()
            ->will($this->returnValue($selectMock));

        $collection = new Mage_Webhook_Model_Resource_Job_Collection($mockFetchStrategy);
        $collection->setConnection($connMock);
        $this->assertInstanceOf('Mage_Webhook_Model_Resource_Job_Collection', $collection->setPageLimit());
    }

    /**
     * Makes sure that Mage has a mock object manager set, and returns that instance.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _setMageObjectManager()
    {
        Mage::reset();
        $mockObjectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        Mage::setObjectManager($mockObjectManager);

        return $mockObjectManager;
    }
}