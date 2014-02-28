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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

    /** @var \Magento\Core\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $_website;

    /** @var \Magento\Core\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_config;

    /** @var \Magento\Eav\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $_attribute;

    /** @var \Magento\Core\Model\Store\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_coreStoreConfigMock;

    /** @var \Magento\Mail\Template\TransportBuilder|PHPUnit_Framework_MockObject_MockObject */
    protected $_transportBuilderMock;

    /** @var \Magento\Mail\TransportInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $_transportMock;

    /**
     * Set required values
     */
    protected function setUp()
    {
        $this->_website = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $this->_config = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $this->_attribute = $this->getMock('Magento\Eav\Model\Attribute', array(), array(), '', false);
        $this->_storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $this->_storetMock = $this->getMock('\Magento\Core\Model\Store', array(), array(), '', false);
        $this->_coreStoreConfigMock = $this->getMock('\Magento\Core\Model\Store\Config', array(), array(), '', false);
        $this->_transportBuilderMock = $this->getMock(
            '\Magento\Mail\Template\TransportBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->_transportMock = $this->getMock('Magento\Mail\TransportInterface', array(), array(), '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('Magento\Customer\Model\Customer', array(
            'storeManager' => $this->_storeManager,
            'config' =>  $this->_config,
            'transportBuilder' => $this->_transportBuilderMock,
            'coreStoreConfig' => $this->_coreStoreConfigMock
            )
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

        $this->_attribute->expects($this->any())->method('isVisible')->will($this->returnValue(false));

        $this->_storeManager->expects($this->once())
            ->method('getWebsite')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->_website));
        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->with(0)
            ->will($this->returnValue($this->_storetMock));

        $this->_website->expects($this->once())->method('getStoreIds')->will($this->returnValue($storeIds));

        $this->_coreStoreConfigMock
            ->expects($this->at(0))
            ->method('getConfig')
            ->with(\Magento\Customer\Model\Customer::XML_PATH_RESET_PASSWORD_TEMPLATE, $storeId)
            ->will($this->returnValue('templateId'));
        $this->_coreStoreConfigMock
            ->expects($this->at(1))
            ->method('getConfig')
            ->with(\Magento\Customer\Model\Customer::XML_PATH_FORGOT_EMAIL_IDENTITY, $storeId)
            ->will($this->returnValue('sender'));
        $this->_transportBuilderMock
            ->expects($this->once())
            ->method('setTemplateOptions')
            ->will($this->returnSelf());
        $this->_transportBuilderMock
            ->expects($this->once())
            ->method('setTemplateVars')
            ->with(array('customer' => $this->_model, 'store' => $this->_storetMock))
            ->will($this->returnSelf());
        $this->_transportBuilderMock
            ->expects($this->once())
            ->method('addTo')
            ->with($this->equalTo($email), $this->equalTo($firstName . ' ' . $lastName))
            ->will($this->returnSelf());
        $this->_transportBuilderMock
            ->expects($this->once())
            ->method('setFrom')
            ->with('sender')
            ->will($this->returnSelf());
        $this->_transportBuilderMock
            ->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with('templateId')
            ->will($this->returnSelf());
        $this->_transportBuilderMock
            ->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($this->_transportMock));
        $this->_transportMock
            ->expects($this->once())
            ->method('sendMessage');

        $this->_model->sendPasswordResetNotificationEmail();
    }
}
