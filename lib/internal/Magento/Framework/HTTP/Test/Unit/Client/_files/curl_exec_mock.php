<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Client;

use Magento\Framework\HTTP\Test\Unit\Client\CurlMockTest;

/**
 * Override global PHP function
 *
 * @SuppressWarnings("unused")
 * @param mixed $resource
 * @return string
 */
function curl_exec($resource)
{
    return call_user_func(CurlMockTest::$curlExectClosure);
}

/**
 * Extended Curl class with modifications for testing
 */
class CurlMock extends Curl
{
    // @codingStandardsIgnoreStart
    /**
     * Unfortunately, it is necessary for the tests to set this function public.
     *
     * @param resource $ch curl handle, not needed
     * @param string $data
     * @return int
     * @throws \Exception
     */
    public function parseHeaders($ch, $data)
    {
        return parent::parseHeaders($ch, $data);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Return Curl resource, only used for testing.
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->_ch;
    }
}
