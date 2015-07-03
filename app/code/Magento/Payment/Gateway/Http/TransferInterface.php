<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

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
     * @return array
     */
    public function getBody();

    /**
     * Returns URI
     *
     * @return string
     */
    public function getUri();
}
