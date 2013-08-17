<?php
/**
 * Unit test for model Mage_User_Model_User
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
 * Test class for Mage_User_Model_User testing
 */
class Mage_User_Model_UserTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_User_Model_User */
    protected $_model;

    /** @var Mage_Core_Model_Sender|PHPUnit_Framework_MockObject_MockObject */
    protected $_senderMock;

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
        $this->_senderMock = $this->getMockBuilder('Mage_Core_Model_Sender')
            ->disableOriginalConstructor()
            ->setMethods(array('send'))
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

        $this->_model = new Mage_User_Model_User(
            $this->_senderMock, $this->_contextMock, $this->_resourceMock,
            $this->_collectionMock
        );
    }

    public function testSendPasswordResetNotificationEmail()
    {
        $storeId = 0;
        $email = 'test@example.com';
        $firstName = 'Foo';
        $lastName = 'Bar';

        $this->_model->setEmail($email);
        $this->_model->setFirstname($firstName);
        $this->_model->setLastname($lastName);

        $this->_senderMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo($email),
                $this->equalTo($firstName . ' ' . $lastName),
                $this->equalTo(Mage_User_Model_User::XML_PATH_RESET_PASSWORD_TEMPLATE),
                $this->equalTo(Mage_User_Model_User::XML_PATH_FORGOT_EMAIL_IDENTITY),
                $this->equalTo(array('user' => $this->_model)),
                $storeId
            );
        $this->_model->sendPasswordResetNotificationEmail();
    }
}
