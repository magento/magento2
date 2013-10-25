<?php
/**
 * Unit test for customer service layer \Magento\Customer\Model\Customer
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
 * Test class for \Magento\Customer\Model\Customer testing
 */
namespace Magento\Customer\Model;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Customer */
    protected $_model;

    /** @var  \Magento\Customer\Helper\Data */
    protected $_customerData;

    /** @var  \Magento\Core\Helper\Data */
    protected $_coreData;

    /** @var \Magento\Core\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $_website;

    /** @var \Magento\Core\Model\Sender|\PHPUnit_Framework_MockObject_MockObject */
    protected $_senderMock;

    /** @var \Magento\Core\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_config;

    /** @var \Magento\Eav\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $_attribute;

    /** @var \Magento\Core\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $_contextMock;

    /** @var \Magento\Customer\Model\Resource\Customer\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceMock;

    /** @var \Magento\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject */
    protected $_collectionMock;

    /**
     * Set required values
     */
    protected function setUp()
    {
        $this->_customerData = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('getResetPasswordLinkExpirationPeriod'))
            ->getMock();
        $this->_coreData = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $this->_website = $this->getMockBuilder('Magento\Core\Model\Website')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreIds', '__wakeup'))
            ->getMock();
        $this->_senderMock = $this->getMockBuilder('Magento\Core\Model\Sender')
            ->disableOriginalConstructor()
            ->setMethods(array('send'))
            ->getMock();
        $this->_config = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(array('getAttribute'))
            ->getMock();
        $this->_attribute = $this->getMockBuilder('Magento\Eav\Model\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(array('getIsVisible', '__wakeup'))
            ->getMock();
        $this->_resourceMock = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $this->_collectionMock = $this->getMockBuilder('Magento\Data\Collection\Db')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);
        $coreStoreConfig = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false);

        $this->_storeManager = $this->getMockBuilder('Magento\Core\Model\StoreManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getWebsite'))
            ->getMock();
        $this->_contextMock = $this->getMockBuilder('Magento\Core\Model\Context')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreManager'))
            ->getMock();
        $this->_contextMock->expects($this->any())->method('getStoreManager')
            ->will($this->returnValue($this->_storeManager));

        $this->_model = new \Magento\Customer\Model\Customer(
            $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false),
            $this->_customerData,
            $this->_coreData,
            $this->_contextMock,
            $coreRegistry,
            $this->_senderMock,
            $this->_storeManager,
            $this->_config,
            $coreStoreConfig,
            $this->_resourceMock,
            $this->getMock('Magento\Customer\Model\Config\Share', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\AddressFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\Resource\Address\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Email\Template\MailerFactory', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Email\InfoFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\GroupFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\AttributeFactory', array(), array(), '', false),
            $this->_collectionMock,
            array()
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
                $this->equalTo(\Magento\Customer\Model\Customer::XML_PATH_RESET_PASSWORD_TEMPLATE),
                $this->equalTo(\Magento\Customer\Model\Customer::XML_PATH_FORGOT_EMAIL_IDENTITY),
                $this->equalTo(array('customer' => $this->_model)),
                $storeId
        );
        $this->_model->sendPasswordResetNotificationEmail();
    }
}
