<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for encrypting data.
 */
class Cryptographer
{
    /**
     * Resource for handling MBI token value.
     *
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * Cipher method for encryption.
     *
     * @var string
     */
    private $cipherMethod = 'AES-256-CBC';

    /**
     * @var EncodedContextFactory
     */
    private $encodedContextFactory;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param EncodedContextFactory $encodedContextFactory
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        EncodedContextFactory $encodedContextFactory
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->encodedContextFactory = $encodedContextFactory;
    }

    /**
     * Encrypt input data.
     *
     * @param string $source
     * @return EncodedContext
     * @throws LocalizedException
     */
    public function encode($source)
    {
        if (!is_string($source)) {
            try {
                $source = (string)$source;
            } catch (\Exception $e) {
                throw new LocalizedException(__('Input data must be string or convertible into string.'));
            }
        } elseif (!$source) {
            throw new LocalizedException(__('Input data must be non-empty string.'));
        }
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__('Not valid cipher method.'));
        }
        $initializationVector = $this->getInitializationVector();

        $encodedContext = $this->encodedContextFactory->create([
            'content' => openssl_encrypt(
                $source,
                $this->cipherMethod,
                $this->getKey(),
                OPENSSL_RAW_DATA,
                $initializationVector
            ),
            'initializationVector' => $initializationVector,
        ]);

        return $encodedContext;
    }

    /**
     * Return key for encryption.
     *
     * Random initial value for key used in case of empty token value to prevent a vulnerability with a predicted key.
     *
     * @return string
     */
    private function getKey()
    {
        return hash('sha256', $this->analyticsToken->getToken() ?: openssl_random_pseudo_bytes(256));
    }

    /**
     * Return established cipher method.
     *
     * @return string
     */
    private function getCipherMethod()
    {
        return $this->cipherMethod;
    }

    /**
     * Return each time generated random initialization vector which depends on the cipher method.
     *
     * @return string
     */
    private function getInitializationVector()
    {
        $ivSize = openssl_cipher_iv_length($this->getCipherMethod());
        return openssl_random_pseudo_bytes($ivSize);
    }

    /**
     * Check that cipher method is allowed for encryption.
     *
     * @param string $cipherMethod
     * @return bool
     */
    private function validateCipherMethod($cipherMethod)
    {
        $methods = openssl_get_cipher_methods();
        return (false !== array_search($cipherMethod, $methods));
    }
}
