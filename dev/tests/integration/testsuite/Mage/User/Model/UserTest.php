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
 * @category    Magento
 * @package     Mage_User
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_User
 */
class Mage_User_Model_UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_User_Model_User
     */
    protected $_model;

    /**
     * @var Mage_User_Model_Role
     */
    protected static $_newRole;

    protected function setUp()
    {
        $this->_model = new Mage_User_Model_User;
    }

    /**
     * Empty fixture to wrap tests in db transaction
     */
    public static function emptyFixture()
    {

    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testCRUD()
    {
        $this->_model->setFirstname("John")
            ->setLastname("Doe")
            ->setUsername('user2')
            ->setPassword(Magento_Test_Bootstrap::ADMIN_PASSWORD)
            ->setEmail('user@magento.com');

        $crud = new Magento_Test_Entity($this->_model, array('firstname' => '_New_name_'));
        $crud->testCrud();
    }

    /**
     * Ensure that an exception is not thrown, if the user does not exist
     */
    public function testLoadByUsername()
    {
        $this->_model->loadByUsername('non_existing_user');
        $this->assertNull($this->_model->getId(), 'The admin user has an unexpected ID');
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertNotEmpty($this->_model->getId(), 'The admin user should have been loaded');
    }

    /**
     * Test that user role is updated after save
     *
     * @magentoDataFixture roleDataFixture
     */
    public function testUpdateRoleOnSave()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertEquals('Administrators', $this->_model->getRole()->getRoleName());
        $this->_model->setRoleId(self::$_newRole->getId())->save();
        $this->assertEquals('admin_role', $this->_model->getRole()->getRoleName());
    }

    public static function roleDataFixture()
    {
        self::$_newRole = new Mage_User_Model_Role;
        self::$_newRole->setName('admin_role')
            ->setRoleType('G')
            ->setPid('1');
        self::$_newRole->save();
    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testSaveExtra()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->_model->saveExtra(array('test' => 'val'));
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $extra = unserialize($this->_model->getExtra());
        $this->assertEquals($extra['test'], 'val');
    }

    /**
     * @magentoDataFixture roleDataFixture
     */
    public function testGetRoles()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $roles = $this->_model->getRoles();
        $this->assertEquals(1, count($roles));
        $this->assertEquals(1, $roles[0]);
        $this->_model->setRoleId(self::$_newRole->getId())->save();
        $roles = $this->_model->getRoles();
        $this->assertEquals(1, count($roles));
        $this->assertEquals(self::$_newRole->getId(), $roles[0]);
    }

    /**
     * @magentoDataFixture roleDataFixture
     */
    public function testGetRole()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $role = $this->_model->getRole();
        $this->assertInstanceOf('Mage_User_Model_Role', $role);
        $this->assertEquals(1, $role->getId());
        $this->_model->setRoleId(self::$_newRole->getId())->save();
        $role = $this->_model->getRole();
        $this->assertEquals(self::$_newRole->getId(), $role->getId());
    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testDeleteFromRole()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->_model->setRoleId(1)->deleteFromRole();
        $role = $this->_model->getRole();
        $this->assertNull($role->getId());
    }

    public function testRoleUserExists()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->_model->setRoleId(1);
        $this->assertTrue($this->_model->roleUserExists());
        $this->_model->setRoleId(2);
        $this->assertFalse($this->_model->roleUserExists());
    }

    /**
     * @dataProvider existingUserProvider
     */
    public function testUserExists($username, $email)
    {
        $this->_model->setUsername($username)
            ->setEmail($email);
        $this->assertTrue($this->_model->userExists());
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertFalse($this->_model->userExists());
    }

    public function existingUserProvider()
    {
        return array(
            array('user', 'user@magento.com'),
            array('user1', 'admin@example.com'),
        );
    }

    public function testGetCollection()
    {
        $this->assertInstanceOf('Mage_Core_Model_Resource_Db_Collection_Abstract', $this->_model->getCollection());
    }

    public function testSendPasswordResetConfirmationEmail()
    {
        $mailer = $this->getMock('Mage_Core_Model_Email_Template_Mailer');
        $mailer->expects($this->once())
            ->method('setTemplateId')
            ->with(Mage::getStoreConfig(Mage_User_Model_User::XML_PATH_FORGOT_EMAIL_TEMPLATE));
        $mailer->expects($this->once())
            ->method('send');
        $this->_model->setMailer($mailer);
        $this->_model->sendPasswordResetConfirmationEmail();
    }

    public function testGetName()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertEquals('firstname lastname', $this->_model->getName());
        $this->assertEquals('firstname///lastname', $this->_model->getName('///'));
    }

    public function testGetAclRole()
    {
        $newuser = new Mage_User_Model_User();
        $newuser->setUserId(10);
        $this->assertNotEquals($this->_model->getAclRole(), $newuser->getAclRole());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store admin/security/use_case_sensitive_login 1
     */
    public function testAuthenticate()
    {
        $this->assertFalse($this->_model->authenticate('User', Magento_Test_Bootstrap::ADMIN_PASSWORD));
        $this->assertTrue($this->_model->authenticate(
                Magento_Test_Bootstrap::ADMIN_NAME,
                Magento_Test_Bootstrap::ADMIN_PASSWORD
            )
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store admin/security/use_case_sensitive_login 0
     */
    public function testAuthenticateCaseInsensitive()
    {
        $this->assertTrue($this->_model->authenticate('user', Magento_Test_Bootstrap::ADMIN_PASSWORD));
        $this->assertTrue($this->_model->authenticate(
                Magento_Test_Bootstrap::ADMIN_NAME,
                Magento_Test_Bootstrap::ADMIN_PASSWORD
            )
        );
    }

    /**
     * @expectedException Mage_Backend_Model_Auth_Exception
     * @magentoDataFixture emptyFixture
     */
    public function testAuthenticateInactiveUser()
    {
        $this->_model->load(1);
        $this->_model->setIsActive(0)->save();
        $this->_model->authenticate(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
    }

    /**
     * @expectedException Mage_Backend_Model_Auth_Exception
     * @magentoDataFixture emptyFixture
     */
    public function testAuthenticateUserWithoutRole()
    {
        $this->_model->load(1);
        $this->_model->setRoleId(1)->deleteFromRole();
        $this->_model->authenticate(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testLoginsAreLogged()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $lognum = $this->_model->getLognum();

        $beforeLogin = time();
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD)
            ->reload();
        $loginTime = strtotime($this->_model->getLogdate());

        $this->assertTrue($beforeLogin <= $loginTime && $loginTime <= time() );
        $this->assertEquals(++$lognum, $this->_model->getLognum());

        $beforeLogin = time();
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD)
            ->reload();
        $loginTime = strtotime($this->_model->getLogdate());
        $this->assertTrue($beforeLogin <= $loginTime && $loginTime <= time() );
        $this->assertEquals(++$lognum, $this->_model->getLognum());
    }

    public function testReload()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->_model->setFirstname('NewFirstName');
        $this->assertEquals('NewFirstName', $this->_model->getFirstname());
        $this->_model->reload();
        $this->assertEquals('firstname', $this->_model->getFirstname());
    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testHasAssigned2Role()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $role = $this->_model->hasAssigned2Role($this->_model);
        $this->assertEquals(1, count($role));
        $this->assertArrayHasKey('role_id', $role[0]);
        $this->_model->setRoleId(1)->deleteFromRole();
        $this->assertEmpty($this->_model->hasAssigned2Role($this->_model));
    }

    public function testValidateEmptyUserName()
    {
        $errors = $this->_model->validate();
        $this->assertContains(Mage::helper('Mage_User_Helper_Data')->__('User Name is required field.'), $errors);
    }

    public function testValidateEmptyFirstName()
    {
        $errors = $this->_model->validate();
        $this->assertContains(Mage::helper('Mage_User_Helper_Data')->__('First Name is required field.'), $errors);
    }

    public function testValidateEmptyLastName()
    {
        $errors = $this->_model->validate();
        $this->assertContains(Mage::helper('Mage_User_Helper_Data')->__('First Name is required field.'), $errors);
    }

    public function testValidateInvalidEmail()
    {
        $this->_model->setEmail('invalid@email');
        $errors = $this->_model->validate();
        $this->assertContains(Mage::helper('Mage_User_Helper_Data')->__('Please enter a valid email.'), $errors);
    }

    public function testValidatePasswordsDontMatch()
    {
        $this->_model->setNewPassword('password');
        $this->_model->setPasswordConfirmation('password1');
        $errors = $this->_model->validate();
        $this->assertContains(
            Mage::helper('Mage_User_Helper_Data')->__('Password confirmation must be same as password.'),
            $errors
        );
    }

    public function testValidatePasswordTooShort()
    {
        $this->_model->setNewPassword('123456');
        $errors = $this->_model->validate();
        $this->assertContains(
            Mage::helper('Mage_User_Helper_Data')->__(
                'Password must be at least of %d characters.', Mage_User_Model_User::MIN_PASSWORD_LENGTH
            ),
            $errors
        );
    }

    /**
     * @dataProvider providerInvalidUserPasswords
     * @param string $password
     */
    public function testValidateInvalidPassword($password)
    {
        $this->_model->setNewPassword($password);
        $errors = $this->_model->validate();
        $this->assertContains(
            Mage::helper('Mage_User_Helper_Data')->__('Password must include both numeric and alphabetic characters.'),
            $errors
        );
    }

    public function providerInvalidUserPasswords()
    {
        return array(array('aaaaaaaa'), array('1234567'));
    }

    public function testValidateExistingUser()
    {
        $this->_model->setUsername('user');
        $errors = $this->_model->validate();
        $this->assertContains(
            Mage::helper('Mage_User_Helper_Data')->__('A user with the same user name or email aleady exists.'),
            $errors
        );
    }

    public function testValidateOk()
    {
        $this->_model->setUsername('user1')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('jdoe@gmail.com')
            ->setNewPassword('1234abc')
            ->setPasswordConfirmation('1234abc');
        $this->assertTrue($this->_model->validate());
    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testChangeResetPasswordLinkToken()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->_model->changeResetPasswordLinkToken('test');
        $date = $this->_model->getRpTokenCreatedAt();
        $this->assertNotNull($date);
        $this->_model->save();
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertEquals('test', $this->_model->getRpToken());
        $this->assertEquals(strtotime($date), strtotime($this->_model->getRpTokenCreatedAt()));
    }

    /**
     * @magentoDataFixture emptyFixture
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/admin/emails/password_reset_link_expiration_period 10
     */
    public function testIsResetPasswordLinkTokenExpired()
    {
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertTrue($this->_model->isResetPasswordLinkTokenExpired());
        $this->_model->changeResetPasswordLinkToken('test');
        $this->_model->save();
        $this->_model->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->assertFalse($this->_model->isResetPasswordLinkTokenExpired());
        $this->_model->setRpTokenCreatedAt(Varien_Date::formatDate(time() - 60 * 60 * 24 * 10 + 10));
        $this->assertFalse($this->_model->isResetPasswordLinkTokenExpired());

        $this->_model->setRpTokenCreatedAt(Varien_Date::formatDate(time() - 60 * 60 * 24 * 10 - 10));
        $this->assertTrue($this->_model->isResetPasswordLinkTokenExpired());
    }

    public function testGetSetHasAvailableResources()
    {
        $this->_model->setHasAvailableResources(true);
        $this->assertTrue($this->_model->hasAvailableResources());

        $this->_model->setHasAvailableResources(false);
        $this->assertFalse($this->_model->hasAvailableResources());
    }
}
