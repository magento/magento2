<?php
/**
 * REST web API authentication model.
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
class Mage_Webapi_Controller_Dispatcher_Rest_Authentication
{
    /** @var Mage_Webapi_Model_Authorization_RoleLocator */
    protected $_roleLocator;

    /** @var Mage_Webapi_Model_Rest_Oauth_Server */
    protected $_oauthServer;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Model_Rest_Oauth_Server $oauthServer
     * @param Mage_Webapi_Model_Authorization_RoleLocator $roleLocator
     */
    public function __construct(
        Mage_Webapi_Model_Rest_Oauth_Server $oauthServer,
        Mage_Webapi_Model_Authorization_RoleLocator $roleLocator
    ) {
        $this->_oauthServer = $oauthServer;
        $this->_roleLocator = $roleLocator;
    }

    /**
     * Authenticate user.
     *
     * @throws Mage_Webapi_Exception If authentication failed
     */
    public function authenticate()
    {
        try {
            $consumer = $this->_oauthServer->authenticateTwoLegged();
            $this->_roleLocator->setRoleId($consumer->getRoleId());
        } catch (Exception $e) {
            throw new Mage_Webapi_Exception(
                $this->_oauthServer->reportProblem($e),
                Mage_Webapi_Exception::HTTP_UNAUTHORIZED
            );
        }
    }
}
