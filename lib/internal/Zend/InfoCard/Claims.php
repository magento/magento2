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
 * @package    Zend_InfoCard
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Claims.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Result value of the InfoCard component, contains any error messages and claims
 * from the processing of an information card.
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Claims
{
    /**
     * Successful validation and extraion of claims
     */
    const RESULT_SUCCESS = 1;

    /**
     * Indicates there was an error processing the XML document
     */
    const RESULT_PROCESSING_FAILURE = 2;

    /**
     * Indicates that the signature values within the XML document failed verification
     */
    const RESULT_VALIDATION_FAILURE = 3;

    /**
     * The default namespace to assume in these claims
     *
     * @var string
     */
    protected $_defaultNamespace  = null;

    /**
     * A boolean indicating if the claims should be consider "valid" or not based on processing
     *
     * @var bool
     */
    protected $_isValid = true;

    /**
     * The error message if any
     *
     * @var string
     */
    protected $_error = "";

    /**
     * An array of claims taken from the information card
     *
     * @var array
     */
    protected $_claims;

    /**
     * The result code of processing the information card as defined by the constants of this class
     *
     * @var integer
     */
    protected $_code;

    /**
     * Override for the safeguard which ensures that you don't use claims which failed validation.
     * Used in situations when there was a validation error you'd like to ignore
     *
     * @return Zend_InfoCard_Claims
     */
    public function forceValid()
    {
        trigger_error("Forcing Claims to be valid although it is a security risk", E_USER_WARNING);
        $this->_isValid = true;
        return $this;
    }

    /**
     * Retrieve the PPI (Private Personal Identifier) associated with the information card
     *
     * @return string the private personal identifier
     */
    public function getCardID()
    {
        return $this->getClaim('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/privatepersonalidentifier');
    }

    /**
     * Retrieves the default namespace used in this information card. If a default namespace was not
     * set, it figures out which one to consider 'default' by taking the first namespace sorted by use-count
     * in claims
     *
     * @throws Zend_InfoCard_Exception
     * @return string The default namespace
     */
    public function getDefaultNamespace()
    {
        if($this->_defaultNamespace === null) {
            $namespaces = array();
            $leader = '';
            foreach($this->_claims as $claim) {
                if(!isset($namespaces[$claim['namespace']])) {
                    $namespaces[$claim['namespace']] = 1;
                } else {
                    $namespaces[$claim['namespace']]++;
                }

                if(empty($leader) || ($namespaces[$claim['namespace']] > $leader)) {
                    $leader = $claim['namespace'];
                }
            }

            if(empty($leader)) {
                #require_once 'Zend/InfoCard/Exception.php';
                throw new Zend_InfoCard_Exception("Failed to determine default namespace");
            }

            $this->setDefaultNamespace($leader);
        }

        return $this->_defaultNamespace;
    }

    /**
     * Set the default namespace, overriding any existing default
     *
     * @throws Zend_InfoCard_Exception
     * @param string $namespace The default namespace to use
     * @return Zend_InfoCard_Claims
     */
    public function setDefaultNamespace($namespace)
    {

        foreach($this->_claims as $claim) {
            if($namespace == $claim['namespace']) {
                $this->_defaultNamespace = $namespace;
                return $this;
            }
        }

        #require_once 'Zend/InfoCard/Exception.php';
        throw new Zend_InfoCard_Exception("At least one claim must exist in specified namespace to make it the default namespace");
    }

    /**
     * Indicates if this claim object contains validated claims or not
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->_isValid;
    }

    /**
     * Set the error message contained within the claims object
     *
     * @param string $error The error message
     * @return Zend_InfoCard_Claims
     */
    public function setError($error)
    {
        $this->_error = $error;
        $this->_isValid = false;
        return $this;
    }

    /**
     * Retrieve the error message contained within the claims object
     *
     * @return string The error message
     */
    public function getErrorMsg()
    {
        return $this->_error;
    }

    /**
     * Set the claims for the claims object. Can only be set once and is done
     * by the component itself. Internal use only.
     *
     * @throws Zend_InfoCard_Exception
     * @param array $claims
     * @return Zend_InfoCard_Claims
     */
    public function setClaims(Array $claims)
    {
        if($this->_claims !== null) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Claim objects are read-only");
        }

        $this->_claims = $claims;
        return $this;
    }

    /**
     * Set the result code of the claims object.
     *
     * @throws Zend_InfoCard_Exception
     * @param int $code The result code
     * @return Zend_InfoCard_Claims
     */
    public function setCode($code)
    {
        switch($code) {
            case self::RESULT_PROCESSING_FAILURE:
            case self::RESULT_SUCCESS:
            case self::RESULT_VALIDATION_FAILURE:
                $this->_code = $code;
                return $this;
        }

        #require_once 'Zend/InfoCard/Exception.php';
        throw new Zend_InfoCard_Exception("Attempted to set unknown error code");
    }

    /**
     * Gets the result code of the claims object
     *
     * @return integer The result code
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get a claim by providing its complete claim URI
     *
     * @param string $claimURI The complete claim URI to retrieve
     * @return mixed The claim matching that specific URI or null if not found
     */
    public function getClaim($claimURI)
    {
        if($this->claimExists($claimURI)) {
            return $this->_claims[$claimURI]['value'];
        }

        return null;
    }

    /**
     * Indicates if a specific claim URI exists or not within the object
     *
     * @param string $claimURI The complete claim URI to check
     * @return bool true if the claim exists, false if not found
     */
    public function claimExists($claimURI)
    {
        return isset($this->_claims[$claimURI]);
    }

    /**
     * Magic helper function
     * @throws Zend_InfoCard_Exception
     */
    public function __unset($k)
    {
        #require_once 'Zend/InfoCard/Exception.php';
        throw new Zend_InfoCard_Exception("Claim objects are read-only");
    }

    /**
     * Magic helper function
     */
    public function __isset($k)
    {
        return $this->claimExists("{$this->getDefaultNamespace()}/$k");
    }

    /**
     * Magic helper function
     */
    public function __get($k)
    {
        return $this->getClaim("{$this->getDefaultNamespace()}/$k");
    }

    /**
     * Magic helper function
     * @throws Zend_InfoCard_Exception
     */
    public function __set($k, $v)
    {
        #require_once 'Zend/InfoCard/Exception.php';
        throw new Zend_InfoCard_Exception("Claim objects are read-only");
    }
}
