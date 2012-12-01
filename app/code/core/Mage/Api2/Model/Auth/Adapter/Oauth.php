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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * oAuth Authentication adapter
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Auth_Adapter_Oauth extends Mage_Api2_Model_Auth_Adapter_Abstract
{
    /**
     * Process request and figure out an API user type and its identifier
     *
     * Returns stdClass object with two properties: type and id
     *
     * @param Mage_Api2_Model_Request $request
     * @return stdClass
     */
    public function getUserParams(Mage_Api2_Model_Request $request)
    {
        /** @var $oauthServer Mage_Oauth_Model_Server */
        $oauthServer   = Mage::getModel('Mage_Oauth_Model_Server', array('request' => $request));
        $userParamsObj = (object) array('type' => null, 'id' => null);

        try {
            $token    = $oauthServer->checkAccessRequest();
            $userType = $token->getUserType();

            if (Mage_Oauth_Model_Token::USER_TYPE_ADMIN == $userType) {
                $userParamsObj->id = $token->getAdminId();
            } else {
                $userParamsObj->id = $token->getCustomerId();
            }
            $userParamsObj->type = $userType;
        } catch (Exception $e) {
            throw new Mage_Api2_Exception($oauthServer->reportProblem($e), Mage_Api2_Model_Server::HTTP_UNAUTHORIZED);
        }
        return $userParamsObj;
    }

    /**
     * Check if request contains authentication info for adapter
     *
     * @param Mage_Api2_Model_Request $request
     * @return boolean
     */
    public function isApplicableToRequest(Mage_Api2_Model_Request $request)
    {
        $headerValue = $request->getHeader('Authorization');

        return $headerValue && 'oauth' === strtolower(substr($headerValue, 0, 5));
    }
}
