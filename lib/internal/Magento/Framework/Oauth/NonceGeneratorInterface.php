<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth;

/**
 * NonceGeneratorInterface provides methods for generating a nonce for a consumer and validating a nonce to ensure
 * that it is not already used by an existing consumer. Validation will persist the nonce if validation succeeds.
 * A method for generating a current timestamp is also provided by this interface.
 *
 * @api
 * @since 2.0.0
 */
interface NonceGeneratorInterface
{
    /**
     * Generate a new nonce for the consumer (if consumer is specified).
     *
     * @param ConsumerInterface $consumer
     * @return string The generated nonce value.
     * @since 2.0.0
     */
    public function generateNonce(ConsumerInterface $consumer = null);

    /**
     * Generate a current timestamp.
     *
     * @return int The time as an int
     * @since 2.0.0
     */
    public function generateTimestamp();

    /**
     * Validate the specified nonce, which ensures that it can only be used by a single consumer and persist it
     * with the specified consumer and timestamp. This method effectively saves the nonce and marks it as used
     * by the specified consumer.
     *
     * @param ConsumerInterface $consumer
     * @param string $nonce The nonce value.
     * @param int $timestamp The 'oauth_timestamp' value.
     * @return void
     * @throws \Magento\Framework\Oauth\Exception Exceptions are thrown for validation errors.
     * @since 2.0.0
     */
    public function validateNonce(ConsumerInterface $consumer, $nonce, $timestamp);
}
