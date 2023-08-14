<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * Create instance of the mock SoapClient
     *
     */
    public function create(array $data = [])
    {
        return ObjectManager::getInstance()->create(
            MockCurlClient::class,
            [
                'data' => $data
            ]
        );
    }
}
