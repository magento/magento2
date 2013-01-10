<?php
/**
 * Two-legged OAuth server.
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
class Mage_Webapi_Model_Rest_Oauth_Server extends Mage_Oauth_Model_Server
{
    /**
     * Construct server.
     *
     * @param Mage_Webapi_Controller_Request_Rest $request
     * @param Mage_Oauth_Model_Token_Factory $tokenFactory
     * @param Mage_Webapi_Model_Acl_User_Factory $consumerFactory
     * @param Mage_Oauth_Model_Nonce_Factory $nonceFactory
     */
    public function __construct(
        Mage_Webapi_Controller_Request_Rest $request,
        Mage_Oauth_Model_Token_Factory $tokenFactory,
        Mage_Webapi_Model_Acl_User_Factory $consumerFactory,
        Mage_Oauth_Model_Nonce_Factory $nonceFactory
    ) {
        parent::__construct($request, $tokenFactory, $consumerFactory, $nonceFactory);
    }

    /**
     * Authenticate two-legged REST request.
     *
     * @return Mage_Webapi_Model_Acl_User
     */
    public function authenticateTwoLegged()
    {
        // get parameters from request
        $this->_fetchParams();

        // make generic validation of request parameters
        $this->_validateProtocolParams();

        // initialize consumer
        $this->_initConsumer();

        // validate signature
        $this->_validateSignature();

        return $this->_consumer;
    }
}
