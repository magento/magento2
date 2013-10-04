<?php
/**
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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Provides basic logic for hashing passwords and encrypting/decrypting misc data
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model;

class Encryption implements \Magento\Core\Model\EncryptionInterface
{
    const PARAM_CRYPT_KEY = 'crypt.key';

    /**
     * @var \Magento\Crypt
     */
    protected $_crypt;

    /**
     * @var string
     */
    protected $_helper;

    /**
     * @var \Magento\ObjectManager|null
     */
    protected $_objectManager = null;

    /**
     * Cryptographic key
     *
     * @var string
     */
    protected $_cryptKey;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param string $cryptKey
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        $cryptKey
    ) {
        $this->_objectManager = $objectManager;
        $this->_cryptKey = $cryptKey;
    }

    /**
     * Set helper instance
     *
     * @param \Magento\Core\Helper\Data|string $helper
     * @return \Magento\Core\Model\Encryption
     * @throws \InvalidArgumentException
     */
    public function setHelper($helper)
    {
        if (!is_string($helper)) {
            if ($helper instanceof \Magento\Core\Helper\AbstractHelper) {
                $helper = get_class($helper);
            } else {
                throw new \InvalidArgumentException(
                    'Input parameter "$helper" must be either "string" or instance of "Magento\Core\Helper\AbstractHelper"'
                );
            }
        }
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Generate a [salted] hash.
     *
     * $salt can be:
     * false - a random will be generated
     * integer - a random with specified length will be generated
     * string
     *
     * @param string $password
     * @param mixed $salt
     * @return string
     */
    public function getHash($password, $salt = false)
    {
        if (is_integer($salt)) {
            $salt = $this->_objectManager->get($this->_helper)->getRandomString($salt);
        }
        return $salt === false ? $this->hash($password) : $this->hash($salt . $password) . ':' . $salt;
    }

    /**
     * Hash a string
     *
     * @param string $data
     * @return string
     */
    public function hash($data)
    {
        return md5($data);
    }

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @throws \Magento\Core\Exception
     * @return bool
     */
    public function validateHash($password, $hash)
    {
        $hashArr = explode(':', $hash);
        switch (count($hashArr)) {
            case 1:
                return $this->hash($password) === $hash;
            case 2:
                return $this->hash($hashArr[1] . $password) === $hashArr[0];
        }
        throw new \Magento\Core\Exception('Invalid hash.');
    }

    /**
     * Instantiate crypt model
     *
     * @param string $key
     * @return \Magento\Crypt
     */
    protected function _getCrypt($key = null)
    {
        if (null !== $key) {
            return $this->_objectManager->create('Magento\Crypt', array('key' => $key));
        }

        if (!$this->_crypt) {
            $this->_crypt = $this->_objectManager->create('Magento\Crypt', array('key' => $this->_cryptKey));
        }
        
        return $this->_crypt;
    }

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return base64_encode($this->_getCrypt()->encrypt((string)$data));
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return trim($this->_getCrypt()->decrypt(base64_decode((string)$data)));
    }

    /**
     * Return crypt model, instantiate if it is empty
     *
     * @param string $key
     * @return \Magento\Crypt
     */
    public function validateKey($key)
    {
        return $this->_getCrypt($key);
    }
}
