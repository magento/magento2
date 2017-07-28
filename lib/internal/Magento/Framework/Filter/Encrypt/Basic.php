<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Encrypt;

/**
 * Class \Magento\Framework\Filter\Encrypt\Basic
 *
 * @since 2.0.0
 */
class Basic implements \Zend_Filter_Encrypt_Interface
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $encryptor;

    /**
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function decrypt($value)
    {
        return $this->encryptor->encrypt($value);
    }
}
