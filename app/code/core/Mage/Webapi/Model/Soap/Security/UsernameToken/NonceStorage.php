<?php
/**
 * Temporary storage of SOAP WS-Security username token nonce & timestamp.
 *
 * @see http://docs.oasis-open.org/wss-m/wss/v1.1.1/os/wss-UsernameTokenProfile-v1.1.1-os.html
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
class Mage_Webapi_Model_Soap_Security_UsernameToken_NonceStorage
{
    /**
     * Nonce time to life in seconds.
     */
    const NONCE_TTL = 600;

    /**
     * Acceptance time interval for nonce 'from future'. Helps to prevent errors due to time sync issues.
     */
    const NONCE_FROM_FUTURE_ACCEPTABLE_RANGE = 60;

    /**
     * Nonce prefix in cache ID.
     */
    const NONCE_CACHE_ID_PREFIX = 'WEBAPI_NONCE_';

    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_cacheInstance;

    /**
     * Construct nonce storage object.
     *
     * @param Mage_Core_Model_Cache $cacheInstance
     */
    public function __construct(Mage_Core_Model_Cache $cacheInstance)
    {
        $this->_cacheInstance = $cacheInstance;
    }

    /**
     * Validate nonce and timestamp pair.
     * Write nonce to storage if it's valid.
     *
     * @param string $nonce
     * @param int $timestamp
     * @throws Mage_Webapi_Model_Soap_Security_UsernameToken_TimestampRefusedException
     * @throws Mage_Webapi_Model_Soap_Security_UsernameToken_NonceUsedException
     */
    public function validateNonce($nonce, $timestamp)
    {
        $timestamp = (int)$timestamp;
        $isNonceUsed = $timestamp <= (time() - self::NONCE_TTL);
        $isNonceFromFuture = $timestamp > (time() + self::NONCE_FROM_FUTURE_ACCEPTABLE_RANGE);
        if ($timestamp <= 0 || $isNonceUsed || $isNonceFromFuture) {
            throw new Mage_Webapi_Model_Soap_Security_UsernameToken_TimestampRefusedException;
        }

        if ($this->_cacheInstance->load($this->getNonceCacheId($nonce)) == $timestamp) {
            throw new Mage_Webapi_Model_Soap_Security_UsernameToken_NonceUsedException;
        }

        $nonceCacheTtl = self::NONCE_TTL + self::NONCE_FROM_FUTURE_ACCEPTABLE_RANGE;
        $this->_cacheInstance->save(
            $timestamp,
            $this->getNonceCacheId($nonce),
            array(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_TAG),
            $nonceCacheTtl
        );
    }

    /**
     * Generate cache ID for given nonce.
     *
     * @param string $nonce
     * @return string
     */
    public function getNonceCacheId($nonce)
    {
        return hash('md5', self::NONCE_CACHE_ID_PREFIX . $nonce);
    }
}
