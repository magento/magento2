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
 * @version    $Id: Soap.php 22662 2010-07-24 17:37:36Z mabe $
 */

/**
 * @see Zend_Soap_Client
 */
#require_once 'Zend/Soap/Client.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Client_Soap extends Zend_Soap_Client
{
    /**
     * class with credential interface
     *
     * @var Zend_Service_DeveloperGarden_Credential
     */
    private $_credential = null;

    /**
     * WSSE Security Ext Namespace
     *
     * @var string
     */
    const WSSE_NAMESPACE_SECEXT = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * WSSE Saml Namespace
     *
     * @var string
     */
    const WSSE_NAMESPACE_SAML = 'urn:oasis:names:tc:SAML:2.0:assertion';

    /**
     * Security Element
     *
     * @var string
     */
    const WSSE_SECURITY_ELEMENT = 'Security';

    /**
     * UsernameToken Element
     *
     * @var string
     */
    const WSSE_ELEMENT_USERNAMETOKEN = 'UsernameToken';

    /**
     * Usernae Element
     *
     * @var string
     */
    const WSSE_ELEMENT_USERNAME = 'Username';

    /**
     * Password Element
     *
     * @var string
     */
    const WSSE_ELEMENT_PASSWORD = 'Password';

    /**
     * Password Element WSSE Type
     *
     */
    const WSSE_ELEMENT_PASSWORD_TYPE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText';

    /**
     * is this client used by the token service
     *
     * @var Zend_Service_DeveloperGarden_SecurityTokenServer
     */
    protected $_tokenService = null;

    /**
     * Perform a SOAP call but first check for adding STS Token or fetch one
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        /**
         * add WSSE Security header
         */
        if ($this->_tokenService !== null) {
            // if login method we addWsseLoginHeader
            if (in_array('login', $arguments)) {
                $this->addWsseLoginHeader();
            } elseif ($name == 'getTokens') {
                $this->addWsseTokenHeader($this->_tokenService->getLoginToken());
            } else {
                $this->addWsseSecurityTokenHeader($this->_tokenService->getTokens());
            }
        }
        return parent::__call($name, $arguments);
    }

    /**
     * sets the internal handling for handle token service
     *
     * @param Zend_Service_DeveloperGarden_SecurityTokenServer $isTokenService
     * @return Zend_Service_DeveloperGarden_Client_Soap
     */
    public function setTokenService(Zend_Service_DeveloperGarden_SecurityTokenServer $tokenService)
    {
        $this->_tokenService = $tokenService;
        return $this;
    }

    /**
     * returns the currently configured tokenService object
     *
     * @return Zend_Service_DeveloperGarden_SecurityTokenServer
     */
    public function getTokenService()
    {
        return $this->_tokenService;
    }

    /**
     * Sets new credential callback object
     *
     * @param Zend_Service_DeveloperGarden_Credential $credential
     * @return Zend_Service_DeveloperGarden_Client_Soap
     */
    public function setCredential(Zend_Service_DeveloperGarden_Credential $credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * returns the internal credential callback object
     *
     * @return Zend_Service_DeveloperGarden_Credential
     */
    public function getCredential()
    {
        return $this->_credential;
    }

    /**
     * creates the login header and add
     *
     * @return SoapHeader
     */
    public function getWsseLoginHeader()
    {
        $dom = new DOMDocument();

        /**
         * Security Element
         */
        $securityElement = $dom->createElementNS(
            self::WSSE_NAMESPACE_SECEXT,
            'wsse:' . self::WSSE_SECURITY_ELEMENT
        );
        $securityElement->setAttribute('mustUnderstand', true);

        /**
         * Username Token Element
         */
        $usernameTokenElement = $dom->createElementNS(
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_ELEMENT_USERNAMETOKEN
        );

        /**
         * Username Element
         */
        $usernameElement = $dom->createElementNS(
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_ELEMENT_USERNAME,
            $this->_credential->getUsername(true)
        );

        /**
         * Password Element
         */
        $passwordElement = $dom->createElementNS(
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_ELEMENT_PASSWORD,
            $this->_credential->getPassword()
        );
        $passwordElement->setAttribute('Type', self::WSSE_ELEMENT_PASSWORD_TYPE);

        $usernameTokenElement->appendChild($usernameElement);
        $usernameTokenElement->appendChild($passwordElement);

        $securityElement->appendChild($usernameTokenElement);
        $dom->appendChild($securityElement);

        $authSoapVar = new SoapVar(
            $dom->saveXML($securityElement),
            XSD_ANYXML,
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_SECURITY_ELEMENT
        );

        $authSoapHeader = new SoapHeader(
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_SECURITY_ELEMENT,
            $authSoapVar,
            true
        );

        return $authSoapHeader;
    }

    /**
     * creates the token auth header for direct calls
     *
     * @param Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse $token
     * @return SoapHeader
     */
    public function getWsseTokenHeader(
        Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse $token
    ) {
        $format = '<wsse:%s xmlns:wsse="%s" SOAP-ENV:mustUnderstand="1">%s</wsse:%s>';
        $securityHeader = sprintf(
            $format,
            self::WSSE_SECURITY_ELEMENT,
            self::WSSE_NAMESPACE_SECEXT,
            $token->getTokenData(),
            self::WSSE_SECURITY_ELEMENT
        );

        $authSoapVar = new SoapVar(
            $securityHeader,
            XSD_ANYXML,
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_SECURITY_ELEMENT
        );

        $authSoapHeader = new SoapHeader(
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_SECURITY_ELEMENT,
            $authSoapVar,
            true
        );

        return $authSoapHeader;
    }

    /**
     * creates the security token auth header for direct calls
     *
     * @param Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse $token
     * @return SoapHeader
     */
    public function getWsseSecurityTokenHeader(
        Zend_Service_DeveloperGarden_Response_SecurityTokenServer_GetTokensResponse $token
    ) {
        $format = '<wsse:%s xmlns:wsse="%s" SOAP-ENV:mustUnderstand="1">%s</wsse:%s>';
        $securityHeader = sprintf(
            $format,
            self::WSSE_SECURITY_ELEMENT,
            self::WSSE_NAMESPACE_SECEXT,
            $token->getTokenData(),
            self::WSSE_SECURITY_ELEMENT
        );

        $authSoapVar = new SoapVar(
            $securityHeader,
            XSD_ANYXML,
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_SECURITY_ELEMENT
        );

        $authSoapHeader = new SoapHeader(
            self::WSSE_NAMESPACE_SECEXT,
            self::WSSE_SECURITY_ELEMENT,
            $authSoapVar,
            true
        );

        return $authSoapHeader;
    }

    /**
     * adds the login specific header to the client
     *
     * @return Zend_Service_DeveloperGarden_Client_Soap
     */
    public function addWsseLoginHeader()
    {
        return $this->addSoapInputHeader($this->getWsseLoginHeader());
    }

    /**
     * adds the earlier fetched token to the header
     *
     * @param Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse $token
     * @return Zend_Service_DeveloperGarden_Client_Soap
     */
    public function addWsseTokenHeader(
        Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse $token
    ) {
        return $this->addSoapInputHeader($this->getWsseTokenHeader($token));
    }

    /**
     * adds the earlier fetched token to the header
     *
     * @param Zend_Service_DeveloperGarden_Response_SecurityTokenServer_SecurityTokenResponse $token
     * @return Zend_Service_DeveloperGarden_Client_Soap
     */
    public function addWsseSecurityTokenHeader(
        Zend_Service_DeveloperGarden_Response_SecurityTokenServer_GetTokensResponse $token
    ) {
        return $this->addSoapInputHeader($this->getWsseSecurityTokenHeader($token));
    }
}
