<?php
/**
 * This class is capable of creating HMAC signature headers.
 *
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
 * @category    Magento
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Authentication;

class Hmac implements \Magento\Outbound\AuthenticationInterface
{
    /**
     * The name of the header which stores the HMAC signature for client verification
     */
    const HMAC_HEADER = 'Magento-HMAC-Signature';

    /**
     * The name of the header which identifies the domain of the sender to the client
     */
    const DOMAIN_HEADER = 'Magento-Sender-Domain';

    /**
     * 256 bit Secure Hash Algorithm is used by default
     */
    const SHA256_ALGORITHM = 'sha256';

    /** @var \Magento\Core\Model\StoreManagerInterface  */
    private $_storeManager;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Core\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Get authentication signature to add to the headers
     *
     * @param string                         $body
     * @param \Magento\Outbound\UserInterface $user
     *
     * @throws \LogicException
     * @return array Headers to add to message
     */
    public function getSignatureHeaders($body, \Magento\Outbound\UserInterface $user)
    {
        $secret = $user->getSharedSecret();
        if ('' === $secret || is_null($secret)) {
            throw new \LogicException('The shared secret cannot be empty.');
        }

        // Add HMAC Signature
        $signature = hash_hmac(self::SHA256_ALGORITHM, $body, $secret);
        return array(self::DOMAIN_HEADER => $this->_getDomain(), self::HMAC_HEADER => $signature);
    }

    /**
     * An overridable method to get the domain name
     *
     * @return mixed
     */
    protected function _getDomain()
    {
        return parse_url($this->_storeManager->getSafeStore()
            ->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_WEB), PHP_URL_HOST);
    }
}
