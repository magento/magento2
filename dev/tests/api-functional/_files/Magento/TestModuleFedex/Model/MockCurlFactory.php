<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestModuleFedex\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Mock Fedex rest client factory
 */
class MockCurlFactory extends \Magento\Framework\HTTP\Client\CurlFactory
{
    /**
     * Create instance of the mock curlClient
     *
     * @param array $data
     * @return \Magento\Framework\HTTP\Client\Curl
     */
    public function create(array $data = []): \Magento\Framework\HTTP\Client\Curl
    {
        return ObjectManager::getInstance()->create(
            MockCurlClient::class,
            [
                'data' => $data
            ]
        );
    }
}
