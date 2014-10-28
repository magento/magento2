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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Customer */
    protected $_model;

    /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $_website;

    /** @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_config;

    /** @var \Magento\Eav\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $_attribute;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_scopeConfigMock;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $_transportBuilderMock;

    /** @var \Magento\Framework\Mail\TransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_transportMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_encryptor;

    /** @var \Magento\Customer\Model\AttributeFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeFactoryMock;

    /** @var  \Magento\Customer\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeCustomerMock;

    /** @var  \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Customer\Model\Resource\Customer|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    protected function setUp()
    {
        $this->_website = $this->getMock('Magento\Store\Model\Website', array(), array(), '', false);
        $this->_config = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $this->_attribute = $this->getMock('Magento\Eav\Model\Attribute', array(), array(), '', false);
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_storetMock = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_transportBuilderMock = $this->getMock(
            '\Magento\Framework\Mail\Template\TransportBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->_transportMock = $this->getMock(
            'Magento\Framework\Mail\TransportInterface',
            array(),
            array(),
            '',
            false
        );
        $this->attributeFactoryMock = $this->getMock(
            'Magento\Customer\Model\AttributeFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->attributeCustomerMock = $this->getMock(
            'Magento\Customer\Model\Attribute',
            array(),
            array(),
            '',
            false
        );
        $this->resourceMock = $this->getMock(
            '\Magento\Customer\Model\Resource\Customer', //'\Magento\Framework\Object',
            array('getIdFieldName'),
            array(),
            '',
            false,
            false
        );
        $this->resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array('registry'), array(), '', false);
        $this->_encryptor = $this->getMock('Magento\Framework\Encryption\EncryptorInterface');
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Customer\Model\Customer',
            array(
                'storeManager' => $this->_storeManager,
                'config' => $this->_config,
                'transportBuilder' => $this->_transportBuilderMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'encryptor' => $this->_encryptor,
                'attributeFactory' => $this->attributeFactoryMock,
                'registry' => $this->registryMock,
                'resource' => $this->resourceMock,
            )
        );
    }

    public function testHashPassword()
    {
        $this->_encryptor->expects(
            $this->once()
        )->method(
            'getHash'
        )->with(
            'password',
            'salt'
        )->will(
            $this->returnValue('hash')
        );
        $this->assertEquals('hash', $this->_model->hashPassword('password', 'salt'));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
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

        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getWebsite'
        )->with(
            $this->equalTo(1)
        )->will(
            $this->returnValue($this->_website)
        );
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            0
        )->will(
            $this->returnValue($this->_storetMock)
        );

        $this->_website->expects($this->once())->method('getStoreIds')->will($this->returnValue($storeIds));

        $this->_scopeConfigMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            \Magento\Customer\Model\Customer::XML_PATH_RESET_PASSWORD_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )->will(
            $this->returnValue('templateId')
        );
        $this->_scopeConfigMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            \Magento\Customer\Model\Customer::XML_PATH_FORGOT_EMAIL_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )->will(
            $this->returnValue('sender')
        );
        $this->_transportBuilderMock->expects($this->once())->method('setTemplateOptions')->will($this->returnSelf());
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            array('customer' => $this->_model, 'store' => $this->_storetMock)
        )->will(
            $this->returnSelf()
        );
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'addTo'
        )->with(
            $this->equalTo($email),
            $this->equalTo($firstName . ' ' . $lastName)
        )->will(
            $this->returnSelf()
        );
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'setFrom'
        )->with(
            'sender'
        )->will(
            $this->returnSelf()
        );
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateIdentifier'
        )->with(
            'templateId'
        )->will(
            $this->returnSelf()
        );
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'getTransport'
        )->will(
            $this->returnValue($this->_transportMock)
        );
        $this->_transportMock->expects($this->once())->method('sendMessage');

        $this->_model->sendPasswordResetNotificationEmail();
    }

    /**
     * @param $data
     * @param $expected
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate($data, $expected)
    {
        $this->attributeFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->will($this->returnValue($this->attributeCustomerMock));
        $this->attributeCustomerMock->expects($this->exactly(3))
            ->method('loadByCode')
            ->will($this->returnSelf());
        $this->attributeCustomerMock->expects($this->exactly(3))
            ->method('getIsRequired')
            ->will($this->returnValue(true));
        $this->_model->setData($data);
        $this->assertEquals($expected, $this->_model->validate());
    }

    public function validateDataProvider()
    {
        $data = array(
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'email' => 'email@example.com',
            'dob' => '01.01.1970',
            'taxvat' => '10',
            'gender' => 'm',
        );
        return array(
            array(array_diff_key($data, array('firstname' => '')), array('The first name cannot be empty.')),
            array(array_diff_key($data, array('lastname' => '')), array('The last name cannot be empty.')),
            array(array_diff_key($data, array('email' => '')), array('Please correct this email address: "".')),
            array(
                array_merge($data, array('email' => 'wrong@email')),
                array('Please correct this email address: "wrong@email".')
            ),
            array(array_diff_key($data, array('dob' => '')), array('The Date of Birth is required.')),
            array(array_diff_key($data, array('taxvat' => '')), array('The TAX/VAT number is required.')),
            array(array_diff_key($data, array('gender' => '')), array('Gender is required.')),
            array($data, true),
        );
    }

    public function testCanSkipConfirmationWithoutCustomerId()
    {
        $this->registryMock->expects($this->never())->method('registry');
        $this->_model->setData('id', false);
        $this->assertFalse($this->_model->canSkipConfirmation());
    }

    public function testCanSkipConfirmationWithoutSkip()
    {
        $idFieldName = 'id';
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('skip_confirmation_if_email')
            ->will($this->returnValue(false));

        $this->_model->setData($idFieldName, 1);
        $this->assertFalse($this->_model->canSkipConfirmation());
    }

    public function testCanSkipConfirmation()
    {
        $customerEmail = 'test@example.com';
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('skip_confirmation_if_email')
            ->will($this->returnValue($customerEmail));

        $this->_model->setData(array(
            'id' => 1,
            'email' => $customerEmail,
        ));
        $this->assertTrue($this->_model->canSkipConfirmation());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Wrong transactional account email type
     */
    public function testSendNewAccountEmailException()
    {
        $this->_model->sendNewAccountEmail('test');
    }

    public function testSendNewAccountEmailWithoutStoreId()
    {
        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $website = $this->getMock('Magento\Store\Model\Website', array(), array(), '', false);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->will($this->returnValue(array(1,2,3,4)));
        $this->_storeManager->expects($this->once())
            ->method('getWebsite')
            ->with(1)
            ->will($this->returnValue($website));
        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->with(1)
            ->will($this->returnValue($store));

        $this->_config->expects($this->exactly(3))
            ->method('getAttribute')
            ->will($this->returnValue($this->_attribute));

        $this->_attribute->expects($this->exactly(3))
            ->method('getIsVisible')
            ->will($this->returnValue(true));

        $methods = array(
            'setTemplateIdentifier',
            'setTemplateOptions',
            'setTemplateVars',
            'setFrom',
            'addTo',
        );
        foreach ($methods as $method) {
            $this->_transportBuilderMock->expects($this->once())
                ->method($method)
                ->will($this->returnSelf());
        }
        $transportMock = $this->getMock('Magento\Framework\Mail\TransportInterface', array(), array(), '', false);
        $transportMock->expects($this->once())
            ->method('sendMessage')
            ->will($this->returnSelf());
        $this->_transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->_model->setData(array(
                'website_id' => 1,
                'store_id'   => 1,
                'email'      => 'email@example.com',
                'firstname'  => 'FirstName',
                'lastname'   => 'LastName',
                'middlename' => 'MiddleName',
                'prefix'     => 'Prefix',
        ));
        $this->_model->sendNewAccountEmail('registered');
    }
}
