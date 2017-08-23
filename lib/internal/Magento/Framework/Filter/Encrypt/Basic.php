<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Encrypt;

/**
 * Class \Magento\Framework\Filter\Encrypt\Basic
 *
 */
class Basic implements \Zend_Filter_Encrypt_Interface
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(\Magento\Framework\Encryption\EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt value
     *
     * @param string $value
     * @return string
     */
    public function encrypt($value)
    {
        return $this->encryptor->encrypt($value);
    }

    /**
     * Decrypt value
     *
     * @param string $value
     * @return string
     */
    public function decrypt($value)
    {
        return $this->encryptor->encrypt($value);
    }
}
