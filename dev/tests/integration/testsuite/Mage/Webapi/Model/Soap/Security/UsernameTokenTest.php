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
 * @package     Mage_Webapi
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/Webapi/_files/user_with_role.php
 */
class Mage_Webapi_Model_Soap_Security_UsernameTokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /** @var Mage_Webapi_Model_Acl_User */
    protected $_user;

    /**
     * Set up object manager and user factory.
     */
    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_objectManager->addSharedInstance(
            Mage::getObjectManager()->get('Mage_Core_Model_Dir'),
            'Mage_Core_Model_Dir'
        );
        $userFactory = new Mage_Webapi_Model_Acl_User_Factory($this->_objectManager);
        $this->_user = $userFactory->create();
        $this->_user->load('test_username', 'api_key');
    }

    /**
     * Test positive authentication with text password type.
     */
    public function testAuthenticatePasswordText()
    {
        /** @var Mage_Webapi_Model_Soap_Security_UsernameToken $usernameToken */
        $usernameToken = $this->_objectManager->create('Mage_Webapi_Model_Soap_Security_UsernameToken', array(
            'passwordType' => Mage_Webapi_Model_Soap_Security_UsernameToken::PASSWORD_TYPE_TEXT
        ));

        $created = date('c');
        $nonce = base64_encode(mt_rand());
        $authenticatedUser = $usernameToken->authenticate($this->_user->getApiKey(), $this->_user->getSecret(),
            $created, $nonce);
        $this->assertEquals($this->_user->getRoleId(), $authenticatedUser->getRoleId());
    }

    /**
     * Test positive authentication with digest password type.
     */
    public function testAuthenticatePasswordDigest()
    {
        /** @var Mage_Webapi_Model_Soap_Security_UsernameToken $usernameToken */
        $usernameToken = $this->_objectManager->create('Mage_Webapi_Model_Soap_Security_UsernameToken');

        $created = date('c');
        $nonce = mt_rand();
        $password = base64_encode(hash('sha1', $nonce . $created . $this->_user->getSecret(), true));
        $nonce = base64_encode($nonce);
        $authenticatedUser = $usernameToken->authenticate($this->_user->getApiKey(), $password, $created, $nonce);
        $this->assertEquals($this->_user->getRoleId(), $authenticatedUser->getRoleId());
    }

    /**
     * Test negative authentication with used nonce-timestamp pair.
     *
     * @expectedException Mage_Webapi_Model_Soap_Security_UsernameToken_NonceUsedException
     */
    public function testAuthenticateWithNonceUsed()
    {
        /** @var Mage_Webapi_Model_Soap_Security_UsernameToken $usernameToken */
        $usernameToken = $this->_objectManager->create('Mage_Webapi_Model_Soap_Security_UsernameToken');

        $created = date('c');
        $nonce = mt_rand();
        $password = base64_encode(hash('sha1', $nonce . $created . $this->_user->getSecret(), true));
        $nonce = base64_encode($nonce);
        $authenticatedUser = $usernameToken->authenticate($this->_user->getApiKey(), $password, $created, $nonce);
        $this->assertEquals($this->_user, $authenticatedUser);
        // Try to authenticate with the same nonce and timestamp
        $usernameToken->authenticate($this->_user->getApiKey(), $password, $created, $nonce);
    }
}
