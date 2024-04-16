<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
