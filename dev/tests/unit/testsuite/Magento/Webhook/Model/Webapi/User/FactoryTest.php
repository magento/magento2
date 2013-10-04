<?php
/**
 * \Magento\Webhook\Model\Webapi\User\Factory
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
namespace Magento\Webhook\Model\Webapi\User;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webhook\Model\Webapi\User\Factory */
    protected $_userFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockRule;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockUser;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockRole;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockCoreHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockAuthConfig;

    /** @var  array */
    protected $_userContext;

    public function testCreateUser()
    {
        $this->_initializeRoleRuleUser();
        $this->_mockAuthConfig->expects($this->any())
            ->method('getAclVirtualResources')
            ->will($this->returnValue(array()));
        $this->_setupUserService();

        $userId = 'some random user id';
        $this->_mockUser->expects($this->once())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue($userId));

        $this->assertSame($userId, $this->_userFactory->createUser($this->_userContext, array()));

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage some random exception
     */
    public function testCreateUserAndRoleException()
    {
        $this->_initializeRoleRuleUser();
        $this->_mockAuthConfig->expects($this->any())
            ->method('getAclVirtualResources')
            ->will($this->returnValue(array()));
        $this->_setupUserService();

        $exception = new \Exception('some random exception');
        $this->_mockUser->expects($this->once())
            ->method('save')
            ->withAnyParameters()
            ->will($this->throwException($exception));
        $this->_mockRole->expects($this->once())
            ->method('delete');

        $this->_userFactory->createUser($this->_userContext, array());
    }

    public function testInitVirtualResourceMapping()
    {
        $expectedResources = array(
            'resource',
            'webhook/create',
            'webhook/get',
            'webhook/update',
            'webhook/delete',
        );
        $this->_initializeRoleRuleUser();

        $this->_mockAuthConfig->expects($this->once())
            ->method('getAclVirtualResources')
            ->will($this->returnValue(array(array('id' => 'topic', 'parent' => 'resource'))));

        $this->_mockRule->expects($this->once())
            ->method('setResources')
            ->with($this->equalTo($expectedResources))
            ->will($this->returnSelf());
        $this->_setupUserService();

        $this->_userFactory->createUser($this->_userContext, array('topic'));
    }

    protected function _setupUserService()
    {

        $mockRuleFactory = $this->getMockBuilder('Magento\Webapi\Model\Acl\Rule\Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $mockRuleFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_mockRule));


        $mockUserFactory = $this->getMockBuilder('Magento\Webapi\Model\Acl\User\Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $mockUserFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_mockUser));


        $mockRoleFactory = $this->getMockBuilder('Magento\Webapi\Model\Acl\Role\Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $mockRoleFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_mockRole));

        $mockAclCache = $this->getMockBuilder('Magento\Webapi\Model\Acl\Cache')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_userFactory = new \Magento\Webhook\Model\Webapi\User\Factory(
            $mockRuleFactory,
            $mockUserFactory,
            $mockRoleFactory,
            $this->_mockAuthConfig,
            $mockAclCache,
            $this->_mockCoreHelper
        );
    }

    /**
     * Mock Role, Rule, and User for methods that test createUserAndRole
     */
    private function _initializeRoleRuleUser()
    {
        $email = 'test@email.com';
        $key = 'some random key';
        $secret = 'sshhh, don`t tell';
        $company = 'some random company';
        $this->_userContext = array(
            'email'     => $email,
            'key'       => $key,
            'secret'    => $secret,
            'company'   => $company,
        );

        $uniq = 'unique string';

        $this->_mockCoreHelper = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCoreHelper->expects($this->once())
            ->method('uniqHash')
            ->will($this->returnValue($uniq));

        // Mock Role
        $this->_mockRole = $this->getMockBuilder('Magento\Webapi\Model\Acl\Role')
            ->setMethods(array('load', 'save', 'getId', 'setRoleName', 'delete'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockRole->expects($this->once())
            ->method('load')
            ->with($this->equalTo($company . ' - ' . $email), $this->equalTo('role_name'))
            ->will($this->returnSelf());
        $this->_mockRole->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(true));
        $this->_mockRole->expects($this->once())
            ->method('setRoleName')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockRole->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());

        // Mock Rule
        $this->_mockRule = $this->getMockBuilder('Magento\Webapi\Model\Acl\Rule')
            ->disableOriginalConstructor()
            ->setMethods(array('setRoleId', 'setResources', 'saveResources'))
            ->getMock();
        $this->_mockRule->expects($this->once())
            ->method('setRoleId')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockRule->expects($this->once())
            ->method('setResources')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockRule->expects($this->once())
            ->method('saveResources')
            ->withAnyParameters()
            ->will($this->returnSelf());

        // Mock User
        $this->_mockUser = $this->getMockBuilder('Magento\Webapi\Model\Acl\User')
            ->disableOriginalConstructor()
            ->setMethods(
                array('setRoleId', 'setApiKey', 'setSecret', 'setCompanyName', 'setContactEmail', 'save', 'getId')
            )
            ->getMock();
        $this->_mockUser->expects($this->once())
            ->method('setRoleId')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockUser->expects($this->once())
            ->method('setApiKey')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockUser->expects($this->once())
            ->method('setSecret')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockUser->expects($this->once())
            ->method('setCompanyName')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockUser->expects($this->once())
            ->method('setContactEmail')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $this->_mockUser->expects($this->once())
            ->method('save')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_mockAuthConfig = $this->getMockBuilder('Magento\Webapi\Model\Acl\Resource\Provider')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
