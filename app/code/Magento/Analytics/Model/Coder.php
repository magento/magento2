<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;

class Coder
{
    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
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
                throw new LocalizedException(__(''));
            }
        }
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__(''));
        }
        $initializationVector = $this->getInitializationVector();

        $encodedContext = $this->encodedContextFactory->create();
        $encodedContext
            ->setContent(
                openssl_encrypt(
                    $source,
                    $this->cipherMethod,
                    $this->getKey(),
                    OPENSSL_RAW_DATA,
                    $initializationVector
                )
            )
            ->setInitializationVector($initializationVector);

        return $encodedContext;
    }

    /**
     * @return string
     */
    private function getKey()
    {
        return hash('sha256', $this->analyticsToken->getToken());
    }

    /**
     * @return string
     */
    private function getCipherMethod()
    {
        return $this->cipherMethod;
    }

    /**
     * @return string
     */
    private function getInitializationVector()
    {
        $ivSize = openssl_cipher_iv_length($this->getCipherMethod());
        return openssl_random_pseudo_bytes($ivSize);
    }

    /**
     * @param string $cipherMethod
     * @return bool
     */
    private function validateCipherMethod($cipherMethod)
    {
        $methods = openssl_get_cipher_methods();
        return (false !== array_search($cipherMethod, $methods));
    }
}
