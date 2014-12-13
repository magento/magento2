<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
}
