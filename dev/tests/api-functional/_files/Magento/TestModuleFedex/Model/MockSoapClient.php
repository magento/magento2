<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleFedex\Model;

/**
 * Mock Fedex soap client factory
 */
class MockSoapClient extends \SoapClient
{
    /**
     * @var MockResponseBodyLoader
     */
    private $mockResponseBodyLoader;

    /**
     * @param string $wsdl
     * @param MockResponseBodyLoader $mockResponseBodyLoader
     * @param array|null $options
     */
    public function __construct(
        string $wsdl,
        MockResponseBodyLoader $mockResponseBodyLoader,
        array $options = null
    ) {
        parent::__construct($wsdl, $options);
        $this->mockResponseBodyLoader = $mockResponseBodyLoader;
    }

    /**
     * Fetch mock Fedex rates
     *
     * @param array $rateRequest
     * @return \stdClass
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getRates(array $rateRequest): \stdClass
    {
        $response = $this->mockResponseBodyLoader->loadForRequest($rateRequest);

        return json_decode($response);
    }
}
