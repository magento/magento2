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
 * @version    $Id: InfoCard.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Xml_EncryptedData
 */
#require_once 'Zend/InfoCard/Xml/EncryptedData.php';

/**
 * Zend_InfoCard_Xml_Assertion
 */
#require_once 'Zend/InfoCard/Xml/Assertion.php';

/**
 * Zend_InfoCard_Cipher
 */
#require_once 'Zend/InfoCard/Cipher.php';

/**
 * Zend_InfoCard_Xml_Security
 */
#require_once 'Zend/InfoCard/Xml/Security.php';

/**
 * Zend_InfoCard_Adapter_Interface
 */
#require_once 'Zend/InfoCard/Adapter/Interface.php';

/**
 * Zend_InfoCard_Claims
 */
#require_once 'Zend/InfoCard/Claims.php';

/**
 * @category   Zend
 * @package    Zend_InfoCard
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard
{
    /**
     * URI for XML Digital Signature SHA1 Digests
     */
    const DIGEST_SHA1        = 'http://www.w3.org/2000/09/xmldsig#sha1';

    /**
     * An array of certificate pair files and optional passwords for them to search
     * when trying to determine which certificate was used to encrypt the transient key
     *
     * @var Array
     */
    protected $_keyPairs;

    /**
     * The instance to use to decrypt public-key encrypted data
     *
     * @var Zend_InfoCard_Cipher_Pki_Interface
     */
    protected $_pkiCipherObj;

    /**
     * The instance to use to decrypt symmetric encrypted data
     *
     * @var Zend_InfoCard_Cipher_Symmetric_Interface
     */
    protected $_symCipherObj;

    /**
     * The InfoCard Adapter to use for callbacks into the application using the component
     * such as when storing assertions, etc.
     *
     * @var Zend_InfoCard_Adapter_Interface
     */
    protected $_adapter;


    /**
     * InfoCard Constructor
     *
     * @throws Zend_InfoCard_Exception
     */
    public function __construct()
    {
        $this->_keyPairs = array();

        if(!extension_loaded('mcrypt')) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Use of the Zend_InfoCard component requires the mcrypt extension to be enabled in PHP");
        }

        if(!extension_loaded('openssl')) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Use of the Zend_InfoCard component requires the openssl extension to be enabled in PHP");
        }
    }

    /**
     * Sets the adapter uesd for callbacks into the application using the component, used
     * when doing things such as storing / retrieving assertions, etc.
     *
     * @param Zend_InfoCard_Adapter_Interface $a The Adapter instance
     * @return Zend_InfoCard The instnace
     */
    public function setAdapter(Zend_InfoCard_Adapter_Interface $a)
    {
        $this->_adapter = $a;
        return $this;
    }

    /**
     * Retrieves the adapter used for callbacks into the application using the component.
     * If no adapter was set then an instance of Zend_InfoCard_Adapter_Default is used
     *
     * @return Zend_InfoCard_Adapter_Interface The Adapter instance
     */
    public function getAdapter()
    {
        if($this->_adapter === null) {
            #require_once 'Zend/InfoCard/Adapter/Default.php';
            $this->setAdapter(new Zend_InfoCard_Adapter_Default());
        }

        return $this->_adapter;
    }

    /**
     * Gets the Public Key Cipher object used in this instance
     *
     * @return Zend_InfoCard_Cipher_Pki_Interface
     */
    public function getPkiCipherObject()
    {
        return $this->_pkiCipherObj;
    }

    /**
     * Sets the Public Key Cipher Object used in this instance
     *
     * @param Zend_InfoCard_Cipher_Pki_Interface $cipherObj
     * @return Zend_InfoCard
     */
    public function setPkiCipherObject(Zend_InfoCard_Cipher_Pki_Interface $cipherObj)
    {
        $this->_pkiCipherObj = $cipherObj;
        return $this;
    }

    /**
     * Get the Symmetric Cipher Object used in this instance
     *
     * @return Zend_InfoCard_Cipher_Symmetric_Interface
     */
    public function getSymCipherObject()
    {
        return $this->_symCipherObj;
    }

    /**
     * Sets the Symmetric Cipher Object used in this instance
     *
     * @param Zend_InfoCard_Cipher_Symmetric_Interface $cipherObj
     * @return Zend_InfoCard
     */
    public function setSymCipherObject($cipherObj)
    {
        $this->_symCipherObj = $cipherObj;
        return $this;
    }

    /**
     * Remove a Certificate Pair by Key ID from the search list
     *
     * @throws Zend_InfoCard_Exception
     * @param string $key_id The Certificate Key ID returned from adding the certificate pair
     * @return Zend_InfoCard
     */
    public function removeCertificatePair($key_id)
    {

        if(!key_exists($key_id, $this->_keyPairs)) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Attempted to remove unknown key id: $key_id");
        }

        unset($this->_keyPairs[$key_id]);
        return $this;
    }

    /**
     * Add a Certificate Pair to the list of certificates searched by the component
     *
     * @throws Zend_InfoCard_Exception
     * @param string $private_key_file The path to the private key file for the pair
     * @param string $public_key_file The path to the certificate / public key for the pair
     * @param string $type (optional) The URI for the type of key pair this is (default RSA with OAEP padding)
     * @param string $password (optional) The password for the private key file if necessary
     * @return string A key ID representing this key pair in the component
     */
    public function addCertificatePair($private_key_file, $public_key_file, $type = Zend_InfoCard_Cipher::ENC_RSA_OAEP_MGF1P, $password = null)
    {
        if(!file_exists($private_key_file) ||
           !file_exists($public_key_file)) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Could not locate the public and private certificate pair files: $private_key_file, $public_key_file");
        }

        if(!is_readable($private_key_file) ||
           !is_readable($public_key_file)) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Could not read the public and private certificate pair files (check permissions): $private_key_file, $public_key_file");
        }

        $key_id = md5($private_key_file.$public_key_file);

        if(key_exists($key_id, $this->_keyPairs)) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Attempted to add previously existing certificate pair: $private_key_file, $public_key_file");
        }

        switch($type) {
            case Zend_InfoCard_Cipher::ENC_RSA:
            case Zend_InfoCard_Cipher::ENC_RSA_OAEP_MGF1P:
                $this->_keyPairs[$key_id] = array('private' => $private_key_file,
                                'public'      => $public_key_file,
                                'type_uri'    => $type);

                if($password !== null) {
                    $this->_keyPairs[$key_id]['password'] = $password;
                } else {
                    $this->_keyPairs[$key_id]['password'] = null;
                }

                return $key_id;
                break;
            default:
                #require_once 'Zend/InfoCard/Exception.php';
                throw new Zend_InfoCard_Exception("Invalid Certificate Pair Type specified: $type");
        }
    }

    /**
     * Return a Certificate Pair from a key ID
     *
     * @throws Zend_InfoCard_Exception
     * @param string $key_id The Key ID of the certificate pair in the component
     * @return array An array containing the path to the private/public key files,
     *               the type URI and the password if provided
     */
    public function getCertificatePair($key_id)
    {
        if(key_exists($key_id, $this->_keyPairs)) {
            return $this->_keyPairs[$key_id];
        }

        #require_once 'Zend/InfoCard/Exception.php';
        throw new Zend_InfoCard_Exception("Invalid Certificate Pair ID provided: $key_id");
    }

    /**
     * Retrieve the digest of a given public key / certificate using the provided digest
     * method
     *
     * @throws Zend_InfoCard_Exception
     * @param string $key_id The certificate key id in the component
     * @param string $digestMethod The URI of the digest method to use (default SHA1)
     * @return string The digest value in binary format
     */
    protected function _getPublicKeyDigest($key_id, $digestMethod = self::DIGEST_SHA1)
    {
        $certificatePair = $this->getCertificatePair($key_id);

        $temp = file($certificatePair['public']);
        unset($temp[count($temp)-1]);
        unset($temp[0]);
        $certificateData = base64_decode(implode("\n", $temp));

        switch($digestMethod) {
            case self::DIGEST_SHA1:
                $digest_retval = sha1($certificateData, true);
                break;
            default:
                #require_once 'Zend/InfoCard/Exception.php';
                throw new Zend_InfoCard_Exception("Invalid Digest Type Provided: $digestMethod");
        }

        return $digest_retval;
    }

    /**
     * Find a certificate pair based on a digest of its public key / certificate file
     *
     * @param string $digest The digest value of the public key wanted in binary form
     * @param string $digestMethod The URI of the digest method used to calculate the digest
     * @return mixed The Key ID of the matching certificate pair or false if not found
     */
    protected function _findCertifiatePairByDigest($digest, $digestMethod = self::DIGEST_SHA1)
    {

        foreach($this->_keyPairs as $key_id => $certificate_data) {

            $cert_digest = $this->_getPublicKeyDigest($key_id, $digestMethod);

            if($cert_digest == $digest) {
                return $key_id;
            }
        }

        return false;
    }

    /**
     * Extracts the Signed Token from an EncryptedData block
     *
     * @throws Zend_InfoCard_Exception
     * @param string $strXmlToken The EncryptedData XML block
     * @return string The XML of the Signed Token inside of the EncryptedData block
     */
    protected function _extractSignedToken($strXmlToken)
    {
        $encryptedData = Zend_InfoCard_Xml_EncryptedData::getInstance($strXmlToken);

        // Determine the Encryption Method used to encrypt the token

        switch($encryptedData->getEncryptionMethod()) {
            case Zend_InfoCard_Cipher::ENC_AES128CBC:
            case Zend_InfoCard_Cipher::ENC_AES256CBC:
                break;
            default:
                #require_once 'Zend/InfoCard/Exception.php';
                throw new Zend_InfoCard_Exception("Unknown Encryption Method used in the secure token");
        }

        // Figure out the Key we are using to decrypt the token

        $keyinfo = $encryptedData->getKeyInfo();

        if(!($keyinfo instanceof Zend_InfoCard_Xml_KeyInfo_XmlDSig)) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Expected a XML digital signature KeyInfo, but was not found");
        }


        $encryptedKey = $keyinfo->getEncryptedKey();

        switch($encryptedKey->getEncryptionMethod()) {
            case Zend_InfoCard_Cipher::ENC_RSA:
            case Zend_InfoCard_Cipher::ENC_RSA_OAEP_MGF1P:
                break;
            default:
                #require_once 'Zend/InfoCard/Exception.php';
                throw new Zend_InfoCard_Exception("Unknown Key Encryption Method used in secure token");
        }

        $securityTokenRef = $encryptedKey->getKeyInfo()->getSecurityTokenReference();

        $key_id = $this->_findCertifiatePairByDigest($securityTokenRef->getKeyReference());

        if(!$key_id) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Unable to find key pair used to encrypt symmetric InfoCard Key");
        }

        $certificate_pair = $this->getCertificatePair($key_id);

        // Santity Check

        if($certificate_pair['type_uri'] != $encryptedKey->getEncryptionMethod()) {
            #require_once 'Zend/InfoCard/Exception.php';
            throw new Zend_InfoCard_Exception("Certificate Pair which matches digest is not of same algorithm type as document, check addCertificate()");
        }

        $PKcipher = Zend_InfoCard_Cipher::getInstanceByURI($encryptedKey->getEncryptionMethod());

        $base64DecodeSupportsStrictParam = version_compare(PHP_VERSION, '5.2.0', '>=');

        if ($base64DecodeSupportsStrictParam) {
            $keyCipherValueBase64Decoded = base64_decode($encryptedKey->getCipherValue(), true);
        } else {
            $keyCipherValueBase64Decoded = base64_decode($encryptedKey->getCipherValue());
        }

        $symmetricKey = $PKcipher->decrypt(
            $keyCipherValueBase64Decoded,
            file_get_contents($certificate_pair['private']),
            $certificate_pair['password']
            );

        $symCipher = Zend_InfoCard_Cipher::getInstanceByURI($encryptedData->getEncryptionMethod());

        if ($base64DecodeSupportsStrictParam) {
            $dataCipherValueBase64Decoded = base64_decode($encryptedData->getCipherValue(), true);
        } else {
            $dataCipherValueBase64Decoded = base64_decode($encryptedData->getCipherValue());
        }

        $signedToken = $symCipher->decrypt($dataCipherValueBase64Decoded, $symmetricKey);

        return $signedToken;
    }

    /**
     * Process an input Infomation Card EncryptedData block sent from the client,
     * validate it, and return the claims contained within it on success or an error message on error
     *
     * @param string $strXmlToken The XML token sent to the server from the client
     * @return Zend_Infocard_Claims The Claims object containing the claims, or any errors which occurred
     */
    public function process($strXmlToken)
    {

        $retval = new Zend_InfoCard_Claims();

        #require_once 'Zend/InfoCard/Exception.php';
        try {
            $signedAssertionsXml = $this->_extractSignedToken($strXmlToken);
        } catch(Zend_InfoCard_Exception $e) {
            $retval->setError('Failed to extract assertion document');
            $retval->setCode(Zend_InfoCard_Claims::RESULT_PROCESSING_FAILURE);
            return $retval;
        }

        try {
            $assertions = Zend_InfoCard_Xml_Assertion::getInstance($signedAssertionsXml);
        } catch(Zend_InfoCard_Exception $e) {
            $retval->setError('Failure processing assertion document');
            $retval->setCode(Zend_InfoCard_Claims::RESULT_PROCESSING_FAILURE);
            return $retval;
        }

        if(!($assertions instanceof Zend_InfoCard_Xml_Assertion_Interface)) {
            throw new Zend_InfoCard_Exception("Invalid Assertion Object returned");
        }

        if(!($reference_id = Zend_InfoCard_Xml_Security::validateXMLSignature($assertions->asXML()))) {
            $retval->setError("Failure Validating the Signature of the assertion document");
            $retval->setCode(Zend_InfoCard_Claims::RESULT_VALIDATION_FAILURE);
            return $retval;
        }

        // The reference id should be locally scoped as far as I know
        if($reference_id[0] == '#') {
            $reference_id = substr($reference_id, 1);
        } else {
            $retval->setError("Reference of document signature does not reference the local document");
            $retval->setCode(Zend_InfoCard_Claims::RESULT_VALIDATION_FAILURE);
            return $retval;
        }

        // Make sure the signature is in reference to the same document as the assertions
        if($reference_id != $assertions->getAssertionID()) {
            $retval->setError("Reference of document signature does not reference the local document");
            $retval->setCode(Zend_InfoCard_Claims::RESULT_VALIDATION_FAILURE);
        }

        // Validate we haven't seen this before and the conditions are acceptable
        $conditions = $this->getAdapter()->retrieveAssertion($assertions->getAssertionURI(), $assertions->getAssertionID());

        if($conditions === false) {
            $conditions = $assertions->getConditions();
        }


        if(is_array($condition_error = $assertions->validateConditions($conditions))) {
            $retval->setError("Conditions of assertion document are not met: {$condition_error[1]} ({$condition_error[0]})");
            $retval->setCode(Zend_InfoCard_Claims::RESULT_VALIDATION_FAILURE);
        }

        $attributes = $assertions->getAttributes();

        $retval->setClaims($attributes);

        if($retval->getCode() == 0) {
            $retval->setCode(Zend_InfoCard_Claims::RESULT_SUCCESS);
        }

        return $retval;
    }
}
