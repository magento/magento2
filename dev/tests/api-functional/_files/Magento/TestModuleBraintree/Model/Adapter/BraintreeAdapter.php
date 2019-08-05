<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleBraintree\Model\Adapter;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Transaction;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adminhtml\Source\Environment;
use Magento\TestModuleBraintree\Model\MockResponseDataProvider;

/**
 * Class BraintreeAdapter mock for testing
 * Use \Magento\TestModuleBraintree\Model\Adapter\BraintreeAdapterFactory to create new instance of adapter.
 * @codeCoverageIgnore
 */
class BraintreeAdapter extends \Magento\Braintree\Model\Adapter\BraintreeAdapter
{
    /**
     * @var MockResponseDataProvider
     */
    private $mockResponseDataProvider;

    /**
     * @param string $merchantId
     * @param string $publicKey
     * @param string $privateKey
     * @param string $environment
     * @param MockResponseDataProvider $mockResponseDataProvider
     */
    public function __construct(
        $merchantId,
        $publicKey,
        $privateKey,
        $environment,
        MockResponseDataProvider $mockResponseDataProvider
    ) {
        parent::__construct($merchantId, $publicKey, $privateKey, $environment);
        $this->mockResponseDataProvider = $mockResponseDataProvider;
    }

    /**
     * @param string $token
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function createNonce($token)
    {
        return $this->mockResponseDataProvider->generateMockNonceResponse($token);
    }

    /**
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function sale(array $attributes)
    {
        return $this->mockResponseDataProvider->generateMockSaleResponse($attributes);
    }

    /**
     * @param array $params
     * @return string|\Braintree\Result\Error
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate(array $params = [])
    {
        return $this->mockResponseDataProvider->generateMockClientToken();
    }
}
