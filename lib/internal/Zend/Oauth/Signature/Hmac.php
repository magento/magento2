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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Hmac.php 20217 2010-01-12 16:01:57Z matthew $
 */

/** Zend_Oauth_Signature_SignatureAbstract */
#require_once 'Zend/Oauth/Signature/SignatureAbstract.php';

/** Zend_Crypt_Hmac */
#require_once 'Zend/Crypt/Hmac.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Signature_Hmac extends Zend_Oauth_Signature_SignatureAbstract
{
    /**
     * Sign a request
     * 
     * @param  array $params 
     * @param  mixed $method 
     * @param  mixed $url 
     * @return string
     */
    public function sign(array $params, $method = null, $url = null)
    {
        $binaryHash = Zend_Crypt_Hmac::compute(
            $this->_key,
            $this->_hashAlgorithm,
            $this->_getBaseSignatureString($params, $method, $url),
            Zend_Crypt_Hmac::BINARY
        );
        return base64_encode($binaryHash);
    }
}
