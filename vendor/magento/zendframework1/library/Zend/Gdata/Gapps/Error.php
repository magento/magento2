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
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Zend_Gdata_App_Base
 */
#require_once 'Zend/Gdata/App/Base.php';

/**
 * Gdata Gapps Error class. This class is used to represent errors returned
 * within an AppsForYourDomainErrors message received from the Google Apps
 * servers.
 *
 * Several different errors may be represented by this class, determined by
 * the error code returned by the server. For a list of error codes
 * available at the time of this writing, see getErrorCode.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gapps_Error extends Zend_Gdata_App_Base
{

    // Error codes as defined at
    // http://code.google.com/apis/apps/gdata_provisioning_api_v2.0_reference.html#appendix_d

    const UNKNOWN_ERROR = 1000;
    const USER_DELETED_RECENTLY = 1100;
    const USER_SUSPENDED = 1101;
    const DOMAIN_USER_LIMIT_EXCEEDED = 1200;
    const DOMAIN_ALIAS_LIMIT_EXCEEDED = 1201;
    const DOMAIN_SUSPENDED = 1202;
    const DOMAIN_FEATURE_UNAVAILABLE = 1203;
    const ENTITY_EXISTS = 1300;
    const ENTITY_DOES_NOT_EXIST = 1301;
    const ENTITY_NAME_IS_RESERVED = 1302;
    const ENTITY_NAME_NOT_VALID = 1303;
    const INVALID_GIVEN_NAME = 1400;
    const INVALID_FAMILY_NAME = 1401;
    const INVALID_PASSWORD = 1402;
    const INVALID_USERNAME = 1403;
    const INVALID_HASH_FUNCTION_NAME = 1404;
    const INVALID_HASH_DIGEST_LENGTH = 1405;
    const INVALID_EMAIL_ADDRESS = 1406;
    const INVALID_QUERY_PARAMETER_VALUE = 1407;
    const TOO_MANY_RECIPIENTS_ON_EMAIL_LIST = 1500;

    protected $_errorCode = null;
    protected $_reason = null;
    protected $_invalidInput = null;

    public function __construct($errorCode = null, $reason = null,
            $invalidInput = null) {
        parent::__construct("Google Apps error received: $errorCode ($reason)");
        $this->_errorCode = $errorCode;
        $this->_reason = $reason;
        $this->_invalidInput = $invalidInput;
    }

    /**
     * Set the error code for this exception. For more information about
     * error codes, see getErrorCode.
     *
     * @see getErrorCode
     * @param integer $value The new value for the error code.
     */
    public function setErrorCode($value) {
       $this->_errorCode = $value;
    }

    /**
     * Get the error code for this exception. Currently valid values are
     * available as constants within this class. These values are:
     *
     *   UNKNOWN_ERROR (1000)
     *   USER_DELETED_RECENTLY (1100)
     *   USER_SUSPENDED (1101)
     *   DOMAIN_USER_LIMIT_EXCEEDED (1200)
     *   DOMAIN_ALIAS_LIMIT_EXCEEDED (1201)
     *   DOMAIN_SUSPENDED (1202)
     *   DOMAIN_FEATURE_UNAVAILABLE (1203)
     *   ENTITY_EXISTS (1300)
     *   ENTITY_DOES_NOT_EXIST (1301)
     *   ENTITY_NAME_IS_RESERVED (1302)
     *   ENTITY_NAME_NOT_VALID (1303)
     *   INVALID_GIVEN_NAME (1400)
     *   INVALID_FAMILY_NAME (1401)
     *   INVALID_PASSWORD (1402)
     *   INVALID_USERNAME (1403)
     *   INVALID_HASH_FUNCTION_NAME (1404)
     *   INVALID_HASH_DIGEST_LENGTH (1405)
     *   INVALID_EMAIL_ADDRESS (1406)
     *   INVALID_QUERY_PARAMETER_VALUE (1407)
     *   TOO_MANY_RECIPIENTS_ON_EMAIL_LIST (1500)
     *
     * Numbers in parenthesis indicate the actual integer value of the
     * constant. This list should not be treated as exhaustive, as additional
     * error codes may be added at any time.
     *
     * For more information about these codes and their meaning, please
     * see Appendix D of the Google Apps Provisioning API Reference.
     *
     * @link http://code.google.com/apis/apps/gdata_provisioning_api_v2.0_reference.html#appendix_d Google Apps Provisioning API Reference: Appendix D - Gdata Error Codes
     * @see setErrorCode
     * @return integer The error code returned by the Google Apps server.
     */
    public function getErrorCode() {
        return $this->_errorCode;
    }

    /**
     * Set human-readable text describing the reason this exception occurred.
     *
     * @see getReason
     * @param string $value The reason this exception occurred.
     */
    public function setReason($value) {
       $this->_reason = $value;
    }

    /**
     * Get human-readable text describing the reason this exception occurred.
     *
     * @see setReason
     * @return string The reason this exception occurred.
     */
    public function getReason() {
       return $this->_reason;
    }

    /**
     * Set the invalid input which caused this exception.
     *
     * @see getInvalidInput
     * @param string $value The invalid input that triggered this exception.
     */
    public function setInvalidInput($value) {
       $this->_invalidInput = $value;
    }

    /**
     * Set the invalid input which caused this exception.
     *
     * @see setInvalidInput
     * @return string The reason this exception occurred.
     */
    public function getInvalidInput() {
       return $this->_invalidInput;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_errorCode !== null) {
            $element->setAttribute('errorCode', $this->_errorCode);
        }
        if ($this->_reason !== null) {
            $element->setAttribute('reason', $this->_reason);
        }
        if ($this->_invalidInput !== null) {
            $element->setAttribute('invalidInput', $this->_invalidInput);
        }
        return $element;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'errorCode':
            $this->_errorCode = $attribute->nodeValue;
            break;
        case 'reason':
            $this->_reason = $attribute->nodeValue;
            break;
        case 'invalidInput':
            $this->_invalidInput = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get a human readable version of this exception.
     *
     * @return string
     */
    public function __toString() {
        return "Error " . $this->getErrorCode() . ": " . $this->getReason() .
            "\n\tInvalid Input: \"" . $this->getInvalidInput() . "\"";
    }

}
