<?php
/**
 * SOAP web API authentication model.
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
class Mage_Webapi_Controller_Dispatcher_Soap_Authentication
{
    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /**
     * Username token factory.
     *
     * @var Mage_Webapi_Model_Soap_Security_UsernameToken_Factory
     */
    protected $_tokenFactory;

    /** @var Mage_Webapi_Model_Authorization_RoleLocator */
    protected $_roleLocator;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Helper_Data $helper
     * @param Mage_Webapi_Model_Soap_Security_UsernameToken_Factory $usernameTokenFactory
     * @param Mage_Webapi_Model_Authorization_RoleLocator $roleLocator
     */
    public function __construct(
        Mage_Webapi_Helper_Data $helper,
        Mage_Webapi_Model_Soap_Security_UsernameToken_Factory $usernameTokenFactory,
        Mage_Webapi_Model_Authorization_RoleLocator $roleLocator
    ) {
        $this->_helper = $helper;
        $this->_tokenFactory = $usernameTokenFactory;
        $this->_roleLocator = $roleLocator;
    }

    /**
     * Authenticate user.
     *
     * @param stdClass $usernameToken WS-Security UsernameToken object
     * @throws Mage_Webapi_Exception If authentication failed
     */
    public function authenticate($usernameToken)
    {
        try {
            $token = $this->_tokenFactory->createFromArray();
            $request = $usernameToken;
            // @codingStandardsIgnoreStart
            $user = $token->authenticate($request->Username, $request->Password, $request->Created, $request->Nonce);
            // @codingStandardsIgnoreEnd
            $this->_roleLocator->setRoleId($user->getRoleId());
        } catch (Mage_Webapi_Model_Soap_Security_UsernameToken_NonceUsedException $e) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('WS-Security UsernameToken Nonce is already used.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        } catch (Mage_Webapi_Model_Soap_Security_UsernameToken_TimestampRefusedException $e) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('WS-Security UsernameToken Created timestamp is refused.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        } catch (Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidCredentialException $e) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('Invalid Username or Password.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        } catch (Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidDateException $e) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('Invalid UsernameToken Created date.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        }
    }
}
