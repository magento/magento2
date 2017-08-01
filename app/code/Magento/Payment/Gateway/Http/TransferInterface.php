<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Interface TransferInterface
 * @package Magento\Payment\Gateway\Http
 * @api
 * @since 2.0.0
 */
interface TransferInterface
{
    /**
     * Returns gateway client configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getClientConfig();

    /**
     * Returns method used to place request
     *
     * @return string|int
     * @since 2.0.0
     */
    public function getMethod();

    /**
     * Returns headers
     *
     * @return array
     * @since 2.0.0
     */
    public function getHeaders();

    /**
     * Whether body should be encoded before place
     *
     * @return bool
     * @since 2.0.0
     */
    public function shouldEncode();

    /**
     * Returns request body
     *
     * @return array|string
     * @since 2.0.0
     */
    public function getBody();

    /**
     * Returns URI
     *
     * @return string
     * @since 2.0.0
     */
    public function getUri();

    /**
     * Returns Auth username
     *
     * @return string
     * @since 2.0.0
     */
    public function getAuthUsername();

    /**
     * Returns Auth password
     *
     * @return string
     * @since 2.0.0
     */
    public function getAuthPassword();
}
