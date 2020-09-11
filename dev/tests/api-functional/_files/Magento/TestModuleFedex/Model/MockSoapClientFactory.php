<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleFedex\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Mock Fedex soap client factory
 */
class MockSoapClientFactory extends \Magento\Framework\Webapi\Soap\ClientFactory
{
    /**
     * Create instance of the mock SoapClient
     *
     * @param string $wsdl
     * @param array $options
     * @return \SoapClient
     */
    public function create($wsdl, array $options = []): \SoapClient
    {
        return ObjectManager::getInstance()->create(
            MockSoapClient::class,
            [
                'wsdl' => $wsdl,
                'options' => $options,
            ]
        );
    }
}
