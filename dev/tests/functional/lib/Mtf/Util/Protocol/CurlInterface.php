<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
