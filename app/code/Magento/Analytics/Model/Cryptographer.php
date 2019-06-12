<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
<<<<<<< HEAD
                throw new LocalizedException(__('Input data must be string or convertible into string.'));
            }
        } elseif (!$source) {
            throw new LocalizedException(__('Input data must be non-empty string.'));
        }
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__('Not valid cipher method.'));
=======
                throw new LocalizedException(
                    __(
                        'The data is invalid. '
                        . 'Enter the data as a string or data that can be converted into a string and try again.'
                    )
                );
            }
        } elseif (!$source) {
            throw new LocalizedException(__('The data is invalid. Enter the data as a string and try again.'));
        }
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__('The data is invalid. Use a valid cipher method and try again.'));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * @return string
     * @throws LocalizedException
     */
    private function getKey()
    {
        $token = $this->analyticsToken->getToken();
        if (!$token) {
<<<<<<< HEAD
            throw new LocalizedException(__('Encryption key can\'t be empty.'));
=======
            throw new LocalizedException(__('Enter the encryption key and try again.'));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
        return hash('sha256', $token);
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
<<<<<<< HEAD
        $methods = openssl_get_cipher_methods();
=======
        $methods = array_map(
            'strtolower',
            openssl_get_cipher_methods()
        );
        $cipherMethod = strtolower($cipherMethod);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return (false !== array_search($cipherMethod, $methods));
    }
}
