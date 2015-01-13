<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth;

/**
 * Interface ConsumerInterface
 *
 * This interface exposes minimal consumer functionality needed by the Oauth library.
 *
 */
interface ConsumerInterface
{
    /**
     * Validate consumer data (e.g. Key and Secret length).
     *
     * @return bool - True if the consumer data is valid.
     * @throws \Exception
     */
    public function validate();

    /**
     * Get the consumer Id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get consumer key.
     *
     * @return string
     */
    public function getKey();

    /**
     * Get consumer secret.
     *
     * @return string
     */
    public function getSecret();

    /**
     * Get consumer callback Url.
     *
     * @return string
     */
    public function getCallbackUrl();

    /**
     * Get when the consumer was created.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Check if the consumer key has not expired for Oauth token exchange usage
     *
     * @return bool
     */
    public function isValidForTokenExchange();
}
