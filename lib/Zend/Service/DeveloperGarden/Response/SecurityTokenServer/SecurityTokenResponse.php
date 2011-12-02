<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SecurityTokenResponse.php 21189 2010-02-24 01:50:33Z stas $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_ResponseAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ResponseAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_SecurityTokenServer_Interface
 */
#require_once 'Zend/Service/DeveloperGarden/Response/SecurityTokenServer/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse
    extends Zend_Service_DeveloperGarden_Response_ResponseAbstract
    implements Zend_Service_DeveloperGarden_Response_SecurityTokenServer_Interface
{
    /**
     * the token format, should be saml20
     *
     * @var string
     */
    public $tokenFormat = null;

    /**
     * the token encoding, should be text/xml
     *
     * @var string
     */
    public $tokenEncoding = null;

    /**
     * the tokenData should be a valid Assertion value
     *
     * @var unknown_type
     */
    public $tokenData = null;

    /**
     * returns the tokenData
     *
     * @return string
     */
    public function getTokenData()
    {
        if (empty($this->tokenData)) {
            #require_once 'Zend/Service/DeveloperGarden/Response/Exception.php';
            throw new Zend_Service_DeveloperGarden_Response_Exception('No valid tokenData found.');
        }

        return $this->tokenData;
    }

    /**
     * returns the token format value
     *
     * @return string
     */
    public function getTokenFormat()
    {
        return $this->tokenFormat;
    }

    /**
     * returns the token encoding
     *
     * @return string
     */
    public function getTokenEncoding()
    {
        return $this->tokenEncoding;
    }

    /**
     * returns true if the stored token data is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        /**
         * @todo implement the true token validation check
         */
        if (!empty($this->securityTokenData)) {
            return true;
        }
        return false;
    }
}
