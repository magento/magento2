<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model;

/**
 * Test class for \Magento\User\Model\User testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\User */
    protected $_model;

    /** @var \Magento\User\Helper\Data */
    protected $_userData;

    /** @var \Magento\Core\Helper\Data */
    protected $_coreData;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $_transportBuilderMock;

    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $_contextMock;

    /** @var \Magento\User\Model\Resource\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceMock;

    /** @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject */
    protected $_collectionMock;

    /** @var \Magento\Framework\Mail\TransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_transportMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManagerMock;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storetMock;

    /** @var \Magento\Backend\App\ConfigInterface */
    protected $_configMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_encryptorMock;

    /**
     * Set required values
     */
    protected function setUp()
    {
        $this->_userData = $this->getMockBuilder(
            'Magento\User\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_coreData = $this->getMockBuilder(
            'Magento\Core\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_contextMock = $this->getMockBuilder(
            'Magento\Framework\Model\Context'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_resourceMock = $this->getMockBuilder(
            'Magento\User\Model\Resource\User'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_collectionMock = $this->getMockBuilder(
            'Magento\Framework\Data\Collection\Db'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $coreRegistry = $this->getMockBuilder(
            'Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $eventManagerMock = $this->getMockBuilder(
            'Magento\Framework\Event\ManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $objectFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Validator\ObjectFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $roleFactoryMock = $this->getMockBuilder(
            'Magento\Authorization\Model\RoleFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $this->_transportMock = $this->getMockBuilder(
            'Magento\Framework\Mail\TransportInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_transportBuilderMock = $this->getMockBuilder(
            '\Magento\Framework\Mail\Template\TransportBuilder'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_storetMock = $this->getMockBuilder(
            '\Magento\Store\Model\Store'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_storeManagerMock = $this->getMockBuilder(
            '\Magento\Store\Model\StoreManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->_configMock = $this->getMockBuilder(
            '\Magento\Backend\App\ConfigInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->_encryptorMock = $this->getMockBuilder('Magento\Framework\Encryption\EncryptorInterface')
            ->setMethods(['validateHash'])
            ->getMockForAbstractClass();

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\User\Model\User',
            [
                'eventManager' => $eventManagerMock,
                'userData' => $this->_userData,
                'coreData' => $this->_coreData,
                'context' => $this->_contextMock,
                'registry' => $coreRegistry,
                'resource' => $this->_resourceMock,
                'resourceCollection' => $this->_collectionMock,
                'validatorObjectFactory' => $objectFactoryMock,
                'roleFactory' => $roleFactoryMock,
                'transportBuilder' => $this->_transportBuilderMock,
                'storeManager' => $this->_storeManagerMock,
                'config' => $this->_configMock,
                'encryptor' => $this->_encryptorMock
            ]
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

        $this->_configMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_RESET_PASSWORD_TEMPLATE
        )->will(
            $this->returnValue('templateId')
        );
        $this->_configMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY
        )->will(
            $this->returnValue('sender')
        );
        $this->_transportBuilderMock->expects($this->once())->method('setTemplateOptions')->will($this->returnSelf());
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            ['user' => $this->_model, 'store' => $this->_storetMock]
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

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->will(
            $this->returnValue($this->_storetMock)
        );

        $this->assertInstanceOf('\Magento\User\Model\User', $this->_model->sendPasswordResetNotificationEmail());
    }

    public function testSendPasswordResetConfirmationEmail()
    {
        $storeId = 0;
        $email = 'test@example.com';
        $firstName = 'Foo';
        $lastName = 'Bar';

        $this->_model->setEmail($email);
        $this->_model->setFirstname($firstName);
        $this->_model->setLastname($lastName);

        $this->_configMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_TEMPLATE
        )->will(
            $this->returnValue('templateId')
        );
        $this->_configMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY
        )->will(
            $this->returnValue('sender')
        );
        $this->_transportBuilderMock->expects($this->once())->method('setTemplateOptions')->will($this->returnSelf());
        $this->_transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            ['user' => $this->_model, 'store' => $this->_storetMock]
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

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->will(
            $this->returnValue($this->_storetMock)
        );

        $this->assertInstanceOf('\Magento\User\Model\User', $this->_model->sendPasswordResetConfirmationEmail());
    }

    public function testVerifyIdentity()
    {
        $password = 'password';
        $this->_encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->_model->getPassword())
            ->will($this->returnValue(true));
        $this->_model->setIsActive(true);
        $this->_resourceMock->expects($this->once())->method('hasAssigned2Role')->will($this->returnValue(true));
        $this->assertTrue(
            $this->_model->verifyIdentity($password),
            'Identity verification failed while should have passed.'
        );
    }

    public function testVerifyIdentityFailure()
    {
        $password = 'password';
        $this->_encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->_model->getPassword())
            ->will($this->returnValue(false));
        $this->assertFalse(
            $this->_model->verifyIdentity($password),
            'Identity verification passed while should have failed.'
        );
    }

    public function testVerifyIdentityInactiveRecord()
    {
        $password = 'password';
        $this->_encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->_model->getPassword())
            ->will($this->returnValue(true));
        $this->_model->setIsActive(false);
        $this->setExpectedException('Magento\Backend\Model\Auth\Exception', 'This account is inactive.');
        $this->_model->verifyIdentity($password);
    }

    public function testVerifyIdentityNoAssignedRoles()
    {
        $password = 'password';
        $this->_encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->_model->getPassword())
            ->will($this->returnValue(true));
        $this->_model->setIsActive(true);
        $this->_resourceMock->expects($this->once())->method('hasAssigned2Role')->will($this->returnValue(false));
        $this->setExpectedException('Magento\Backend\Model\Auth\Exception', 'Access denied.');
        $this->_model->verifyIdentity($password);
    }
}
