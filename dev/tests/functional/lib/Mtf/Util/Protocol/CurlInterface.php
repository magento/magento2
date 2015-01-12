<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Util\Protocol;

/**
 * Class CurlInterface
 */
interface CurlInterface
{
    /**
     * HTTP request methods
     */
    const GET   = 'GET';

    const POST  = 'POST';

    /**
     * Add additional option to cURL
     *
     * @param  int $option      the CURLOPT_* constants
     * @param  mixed $value
     */
    public function addOption($option, $value);

    /**
     * Send request to the remote server
     *
     * @param string $method
     * @param string $url
     * @param string $httpVer
     * @param array  $headers
     * @param array  $params
     * @return void
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $params = []);

    /**
     * Read response from server
     *
     * @return string
     */
    public function read();

    /**
     * Close the connection to the server
     */
    public function close();
}
