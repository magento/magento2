<?php
/**
 * Unit test for customer service layer Mage_Customer_Model_Customer
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Customer_Model_Customer testing
 */
class Mage_Customer_Model_CustomerTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Customer_Model_Customer */
    protected $_model;

    /** @var Mage_Core_Model_Website|PHPUnit_Framework_MockObject_MockObject */
    protected $_website;

    /** @var Mage_Core_Model_Sender|PHPUnit_Framework_MockObject_MockObject */
    protected $_senderMock;

    /** @var Mage_Core_Model_StoreManager|PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var Mage_Eav_Model_Config|PHPUnit_Framework_MockObject_MockObject */
    protected $_config;

    /** @var Mage_Eav_Model_Attribute|PHPUnit_Framework_MockObject_MockObject */
    protected $_attribute;

    /** @var Mage_Core_Model_Context|PHPUnit_Framework_MockObject_MockObject */
    protected $_contextMock;

    /** @var Mage_Customer_Model_Resource_Customer_Collection|PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceMock;

    /** @var Varien_Data_Collection_Db|PHPUnit_Framework_MockObject_MockObject */
    protected $_collectionMock;

    /**
     * Set required values
     */
    public function setUp()
    {
        $this->_website = $this->getMockBuilder('Mage_Core_Model_Website')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreIds'))
            ->getMock();
        $this->_senderMock = $this->getMockBuilder('Mage_Core_Model_Sender')
            ->disableOriginalConstructor()
            ->setMethods(array('send'))
            ->getMock();
        $this->_storeManager = $this->getMockBuilder('Mage_Core_Model_StoreManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getWebsite'))
            ->getMock();
        $this->_config = $this->getMockBuilder('Mage_Eav_Model_Config')
            ->disableOriginalConstructor()
            ->setMethods(array('getAttribute'))
            ->getMock();
        $this->_attribute = $this->getMockBuilder('Mage_Eav_Model_Attribute')
            ->disableOriginalConstructor()
            ->setMethods(array('getIsVisible'))
            ->getMock();
        $this->_contextMock = $this->getMockBuilder('Mage_Core_Model_Context')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $this->_resourceMock = $this->getMockBuilder('Mage_Customer_Model_Resource_Address')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $this->_collectionMock = $this->getMockBuilder('Varien_Data_Collection_Db')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $this->_model = new Mage_Customer_Model_Customer(
            $this->_contextMock, $this->_senderMock, $this->_storeManager, $this->_config, $this->_resourceMock,
            $this->_collectionMock, array()
        );
    }

    public function testSendPasswordResetConfirmationEmail()
    {
        $storeId = 1;
        $storeIds = array(1);
        $email = 'test@example.com';
        $firstName = 'Foo';
        $lastName = 'Bar';

        $this->_model->setStoreId(0);
        $this->_model->setWebsiteId(1);
        $this->_model->setEmail($email);
        $this->_model->setFirstname($firstName);
        $this->_model->setLastname($lastName);

        $this->_config->expects($this->any())->method('getAttribute')->will($this->returnValue($this->_attribute));

        $this->_attribute->expects($this->any())->method('getIsVisible')->will($this->returnValue(false));

        $this->_storeManager->expects($this->once())
            ->method('getWebsite')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->_website));

        $this->_website->expects($this->once())->method('getStoreIds')->will($this->returnValue($storeIds));

        $this->_senderMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo($email),
                $this->equalTo($firstName . ' ' . $lastName),
                $this->equalTo(Mage_Customer_Model_Customer::XML_PATH_RESET_PASSWORD_TEMPLATE),
                $this->equalTo(Mage_Customer_Model_Customer::XML_PATH_FORGOT_EMAIL_IDENTITY),
                $this->equalTo(array('customer' => $this->_model)),
                $storeId
        );
        $this->_model->sendPasswordResetNotificationEmail();
    }
}
