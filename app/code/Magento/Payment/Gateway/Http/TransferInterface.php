<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Interface TransferInterface
 * @package Magento\Payment\Gateway\Http
 * @api
 */
interface TransferInterface
{
    /**
     * Returns gateway client configuration
     *
     * @return array
     */
    public function getClientConfig();

    /**
     * Returns method used to place request
     *
     * @return string|int
     */
    public function getMethod();

    /**
     * Returns headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Whether body should be encoded before place
     *
     * @return bool
     */
    public function shouldEncode();

    /**
     * Returns request body
     *
     * @return array|string
     */
    public function getBody();

    /**
     * Returns URI
     *
     * @return string
     */
    public function getUri();

    /**
     * Returns Auth username
     *
     * @return string
     */
    public function getAuthUsername();

    /**
     * Returns Auth password
     *
     * @return string
     */
    public function getAuthPassword();
}
