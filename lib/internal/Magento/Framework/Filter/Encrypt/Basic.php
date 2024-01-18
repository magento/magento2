<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Encrypt;

use Laminas\Filter\Encrypt\EncryptionAlgorithmInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Basic implements EncryptionAlgorithmInterface
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(EncryptorInterface $encryptor)
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

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Basic';
    }
}
