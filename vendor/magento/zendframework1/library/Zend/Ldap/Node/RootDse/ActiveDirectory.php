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
 * @package    Zend_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Ldap_Node_RootDse
 */
#require_once 'Zend/Ldap/Node/RootDse.php';

/**
 * Zend_Ldap_Node_RootDse provides a simple data-container for the RootDSE node of
 * an Active Directory server.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_RootDse_ActiveDirectory extends Zend_Ldap_Node_RootDse
{
    /**
     * Gets the configurationNamingContext.
     *
     * @return string|null
     */
    public function getConfigurationNamingContext()
    {
        return $this->getAttribute('configurationNamingContext', 0);
    }

    /**
     * Gets the currentTime.
     *
     * @return string|null
     */
    public function getCurrentTime()
    {
        return $this->getAttribute('currentTime', 0);
    }

    /**
     * Gets the defaultNamingContext.
     *
     * @return string|null
     */
    public function getDefaultNamingContext()
    {
        return $this->getAttribute('defaultNamingContext', 0);
    }

    /**
     * Gets the dnsHostName.
     *
     * @return string|null
     */
    public function getDnsHostName()
    {
        return $this->getAttribute('dnsHostName', 0);
    }

    /**
     * Gets the domainControllerFunctionality.
     *
     * @return string|null
     */
    public function getDomainControllerFunctionality()
    {
        return $this->getAttribute('domainControllerFunctionality', 0);
    }

    /**
     * Gets the domainFunctionality.
     *
     * @return string|null
     */
    public function getDomainFunctionality()
    {
        return $this->getAttribute('domainFunctionality', 0);
    }

    /**
     * Gets the dsServiceName.
     *
     * @return string|null
     */
    public function getDsServiceName()
    {
        return $this->getAttribute('dsServiceName', 0);
    }

    /**
     * Gets the forestFunctionality.
     *
     * @return string|null
     */
    public function getForestFunctionality()
    {
        return $this->getAttribute('forestFunctionality', 0);
    }

    /**
     * Gets the highestCommittedUSN.
     *
     * @return string|null
     */
    public function getHighestCommittedUSN()
    {
        return $this->getAttribute('highestCommittedUSN', 0);
    }

    /**
     * Gets the isGlobalCatalogReady.
     *
     * @return string|null
     */
    public function getIsGlobalCatalogReady()
    {
        return $this->getAttribute('isGlobalCatalogReady', 0);
    }

    /**
     * Gets the isSynchronized.
     *
     * @return string|null
     */
    public function getIsSynchronized()
    {
        return $this->getAttribute('isSynchronized', 0);
    }

    /**
     * Gets the ldapServiceName.
     *
     * @return string|null
     */
    public function getLdapServiceName()
    {
        return $this->getAttribute('ldapServiceName', 0);
    }

    /**
     * Gets the rootDomainNamingContext.
     *
     * @return string|null
     */
    public function getRootDomainNamingContext()
    {
        return $this->getAttribute('rootDomainNamingContext', 0);
    }

    /**
     * Gets the schemaNamingContext.
     *
     * @return string|null
     */
    public function getSchemaNamingContext()
    {
        return $this->getAttribute('schemaNamingContext', 0);
    }

    /**
     * Gets the serverName.
     *
     * @return string|null
     */
    public function getServerName()
    {
        return $this->getAttribute('serverName', 0);
    }

    /**
     * Determines if the capability is supported
     *
     * @param string|string|array $oids capability(s) to check
     * @return boolean
     */
    public function supportsCapability($oids)
    {
        return $this->attributeHasValue('supportedCapabilities', $oids);
    }

    /**
     * Determines if the control is supported
     *
     * @param string|array $oids control oid(s) to check
     * @return boolean
     */
    public function supportsControl($oids)
    {
        return $this->attributeHasValue('supportedControl', $oids);
    }

    /**
     * Determines if the version is supported
     *
     * @param string|array $policies policy(s) to check
     * @return boolean
     */
    public function supportsPolicy($policies)
    {
        return $this->attributeHasValue('supportedLDAPPolicies', $policies);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_ACTIVEDIRECTORY;
    }

    /**
     * Returns the schema DN
     *
     * @return Zend_Ldap_Dn
     */
    public function getSchemaDn()
    {
        $schemaDn = $this->getSchemaNamingContext();
        /**
         * @see Zend_Ldap_Dn
         */
        #require_once 'Zend/Ldap/Dn.php';
        return Zend_Ldap_Dn::fromString($schemaDn);
    }
}
