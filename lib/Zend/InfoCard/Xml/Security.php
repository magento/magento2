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
 * @subpackage Zend_InfoCard_Xml_Security
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Security.php 23280 2010-10-31 10:28:58Z ramon $
 */

/**
 * Zend_InfoCard_Xml_Security_Transform
 */
#require_once 'Zend/InfoCard/Xml/Security/Transform.php';

/**
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml_Security
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Xml_Security
{
    /**
     * ASN.1 type INTEGER class
     */
    const ASN_TYPE_INTEGER = 0x02;

    /**
     * ASN.1 type BIT STRING class
     */
    const ASN_TYPE_BITSTRING = 0x03;

    /**
     * ASN.1 type SEQUENCE class
     */
    const ASN_TYPE_SEQUENCE = 0x30;

    /**
     * The URI for Canonical Method C14N Exclusive
     */
    const CANONICAL_METHOD_C14N_EXC = 'http://www.w3.org/2001/10/xml-exc-c14n#';

    /**
     * The URI for Signature Method SHA1
     */
    const SIGNATURE_METHOD_SHA1 = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';

    /**
     * The URI for Digest Method SHA1
     */
    const DIGEST_METHOD_SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';

    /**
     * The Identifier for RSA Keys
     */
    const RSA_KEY_IDENTIFIER = '300D06092A864886F70D0101010500';

    /**
     * Constructor  (disabled)
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Validates the signature of a provided XML block
     *
     * @param  string $strXMLInput An XML block containing a Signature
     * @return bool True if the signature validated, false otherwise
     * @throws Zend_InfoCard_Xml_Security_Exception
     */
    static public function validateXMLSignature($strXMLInput)
    {
        if(!extension_loaded('openssl')) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("You must have the openssl extension installed to use this class");
        }

        $sxe = simplexml_load_string($strXMLInput);

        if(!isset($sxe->Signature)) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("Could not identify XML Signature element");
        }

        if(!isset($sxe->Signature->SignedInfo)) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("Signature is missing a SignedInfo block");
        }

        if(!isset($sxe->Signature->SignatureValue)) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("Signature is missing a SignatureValue block");
        }

        if(!isset($sxe->Signature->KeyInfo)) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("Signature is missing a KeyInfo block");
        }

        if(!isset($sxe->Signature->KeyInfo->KeyValue)) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("Signature is missing a KeyValue block");
        }

        switch((string)$sxe->Signature->SignedInfo->CanonicalizationMethod['Algorithm']) {
            case self::CANONICAL_METHOD_C14N_EXC:
                $cMethod = (string)$sxe->Signature->SignedInfo->CanonicalizationMethod['Algorithm'];
                break;
            default:
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Unknown or unsupported CanonicalizationMethod Requested");
                break;
        }

        switch((string)$sxe->Signature->SignedInfo->SignatureMethod['Algorithm']) {
            case self::SIGNATURE_METHOD_SHA1:
                $sMethod = (string)$sxe->Signature->SignedInfo->SignatureMethod['Algorithm'];
                break;
            default:
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Unknown or unsupported SignatureMethod Requested");
                break;
        }

        switch((string)$sxe->Signature->SignedInfo->Reference->DigestMethod['Algorithm']) {
            case self::DIGEST_METHOD_SHA1:
                $dMethod = (string)$sxe->Signature->SignedInfo->Reference->DigestMethod['Algorithm'];
                break;
            default:
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Unknown or unsupported DigestMethod Requested");
                break;
        }

        $base64DecodeSupportsStrictParam = version_compare(PHP_VERSION, '5.2.0', '>=');

        if ($base64DecodeSupportsStrictParam) {
            $dValue = base64_decode((string)$sxe->Signature->SignedInfo->Reference->DigestValue, true);
        } else {
            $dValue = base64_decode((string)$sxe->Signature->SignedInfo->Reference->DigestValue);
        }

        if ($base64DecodeSupportsStrictParam) {
            $signatureValue = base64_decode((string)$sxe->Signature->SignatureValue, true);
        } else {
            $signatureValue = base64_decode((string)$sxe->Signature->SignatureValue);
        }

        $transformer = new Zend_InfoCard_Xml_Security_Transform();

        foreach($sxe->Signature->SignedInfo->Reference->Transforms->children() as $transform) {
            $transformer->addTransform((string)$transform['Algorithm']);
        }

        $transformed_xml = $transformer->applyTransforms($strXMLInput);

        $transformed_xml_binhash = pack("H*", sha1($transformed_xml));

        if(!self::_secureStringCompare($transformed_xml_binhash, $dValue)) {
            #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Exception("Locally Transformed XML does not match XML Document. Cannot Verify Signature");
        }

        $public_key = null;

        switch(true) {
            case isset($sxe->Signature->KeyInfo->KeyValue->X509Certificate):

                $certificate = (string)$sxe->Signature->KeyInfo->KeyValue->X509Certificate;


                $pem = "-----BEGIN CERTIFICATE-----\n" .
                       wordwrap($certificate, 64, "\n", true) .
                       "\n-----END CERTIFICATE-----";

                $public_key = openssl_pkey_get_public($pem);

                if(!$public_key) {
                    #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                    throw new Zend_InfoCard_Xml_Security_Exception("Unable to extract and prcoess X509 Certificate from KeyValue");
                }

                break;
            case isset($sxe->Signature->KeyInfo->KeyValue->RSAKeyValue):

                if(!isset($sxe->Signature->KeyInfo->KeyValue->RSAKeyValue->Modulus) ||
                   !isset($sxe->Signature->KeyInfo->KeyValue->RSAKeyValue->Exponent)) {
                       #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                       throw new Zend_InfoCard_Xml_Security_Exception("RSA Key Value not in Modulus/Exponent form");
                }

                $modulus = base64_decode((string)$sxe->Signature->KeyInfo->KeyValue->RSAKeyValue->Modulus);
                $exponent = base64_decode((string)$sxe->Signature->KeyInfo->KeyValue->RSAKeyValue->Exponent);

                $pem_public_key = self::_getPublicKeyFromModExp($modulus, $exponent);

                $public_key = openssl_pkey_get_public ($pem_public_key);

                break;
            default:
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Unable to determine or unsupported representation of the KeyValue block");
        }

        $transformer = new Zend_InfoCard_Xml_Security_Transform();
        $transformer->addTransform((string)$sxe->Signature->SignedInfo->CanonicalizationMethod['Algorithm']);

        // The way we are doing our XML processing requires that we specifically add this
        // (even though it's in the <Signature> parent-block).. otherwise, our canonical form
        // fails signature verification
        $sxe->Signature->SignedInfo->addAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');

        $canonical_signedinfo = $transformer->applyTransforms($sxe->Signature->SignedInfo->asXML());

        if(@openssl_verify($canonical_signedinfo, $signatureValue, $public_key)) {
            return (string)$sxe->Signature->SignedInfo->Reference['URI'];
        }

        return false;
    }

    /**
     * Transform an RSA Key in Modulus/Exponent format into a PEM encoding and
     * return an openssl resource for it
     *
     * @param string $modulus The RSA Modulus in binary format
     * @param string $exponent The RSA exponent in binary format
     * @return string The PEM encoded version of the key
     */
    static protected function _getPublicKeyFromModExp($modulus, $exponent)
    {
        $modulusInteger  = self::_encodeValue($modulus, self::ASN_TYPE_INTEGER);
        $exponentInteger = self::_encodeValue($exponent, self::ASN_TYPE_INTEGER);
        $modExpSequence  = self::_encodeValue($modulusInteger . $exponentInteger, self::ASN_TYPE_SEQUENCE);
        $modExpBitString = self::_encodeValue($modExpSequence, self::ASN_TYPE_BITSTRING);

        $binRsaKeyIdentifier = pack( "H*", self::RSA_KEY_IDENTIFIER );

        $publicKeySequence = self::_encodeValue($binRsaKeyIdentifier . $modExpBitString, self::ASN_TYPE_SEQUENCE);

        $publicKeyInfoBase64 = base64_encode( $publicKeySequence );

        $publicKeyString = "-----BEGIN PUBLIC KEY-----\n";
        $publicKeyString .= wordwrap($publicKeyInfoBase64, 64, "\n", true);
        $publicKeyString .= "\n-----END PUBLIC KEY-----\n";

        return $publicKeyString;
    }

    /**
     * Encode a limited set of data types into ASN.1 encoding format
     * which is used in X.509 certificates
     *
     * @param string $data The data to encode
     * @param const $type The encoding format constant
     * @return string The encoded value
     * @throws Zend_InfoCard_Xml_Security_Exception
     */
    static protected function _encodeValue($data, $type)
    {
        // Null pad some data when we get it (integer values > 128 and bitstrings)
        if( (($type == self::ASN_TYPE_INTEGER) && (ord($data) > 0x7f)) ||
            ($type == self::ASN_TYPE_BITSTRING)) {
                $data = "\0$data";
        }

        $len = strlen($data);

        // encode the value based on length of the string
        // I'm fairly confident that this is by no means a complete implementation
        // but it is enough for our purposes
        switch(true) {
            case ($len < 128):
                return sprintf("%c%c%s", $type, $len, $data);
            case ($len < 0x0100):
                return sprintf("%c%c%c%s", $type, 0x81, $len, $data);
            case ($len < 0x010000):
                return sprintf("%c%c%c%c%s", $type, 0x82, $len / 0x0100, $len % 0x0100, $data);
            default:
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Could not encode value");
        }

        #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
        throw new Zend_InfoCard_Xml_Security_Exception("Invalid code path");
    }

    /**
     * Securely compare two strings for equality while avoided C level memcmp()
     * optimisations capable of leaking timing information useful to an attacker
     * attempting to iteratively guess the unknown string (e.g. password) being
     * compared against.
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    static protected function _secureStringCompare($a, $b)
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result == 0;
    }
}
