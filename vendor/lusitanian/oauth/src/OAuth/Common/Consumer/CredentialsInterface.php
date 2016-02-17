<?php

namespace OAuth\Common\Consumer;

/**
 * Credentials Interface, credentials should implement this.
 */
interface CredentialsInterface
{
    /**
     * @return string
     */
    public function getCallbackUrl();

    /**
     * @return string
     */
    public function getConsumerId();

    /**
     * @return string
     */
    public function getConsumerSecret();
}
