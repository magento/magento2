<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth;

/**
 * Oauth consumer interface.
 *
 * @api
 * @since 2.0.0
 */
interface ConsumerInterface
{
    /**
     * Validate consumer data (e.g. Key and Secret length).
     *
     * @return bool True if the consumer data is valid.
     * @throws \Exception
     * @since 2.0.0
     */
    public function validate();

    /**
     * Get the consumer Id.
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Get consumer key.
     *
     * @return string
     * @since 2.0.0
     */
    public function getKey();

    /**
     * Get consumer secret.
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecret();

    /**
     * Get consumer callback Url.
     *
     * @return string
     * @since 2.0.0
     */
    public function getCallbackUrl();

    /**
     * Get when the consumer was created.
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Check if the consumer key has not expired for Oauth token exchange usage
     *
     * @return bool
     * @since 2.0.0
     */
    public function isValidForTokenExchange();
}
