<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Helper;

use Magento\Framework\HTTP\Client\Curl as CurlLibrary;

class Curl extends CurlLibrary
{
    /**
     * Make DELETE request
     *
     * @param string $uri
     * @param array|string $params
     * @return void
     *
     * @deprecated Replace with the core `delete` implementation
     * @see \Magento\Framework\HTTP\Client\Curl::delete
     */
    public function delete($uri, array|string $params = []): void
    {
        $this->makeRequest("DELETE", $uri, $params);
    }
}
