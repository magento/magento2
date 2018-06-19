<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * This factory is preferable to use for Braintree adapter instance creation.
 */
class BraintreeAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Creates instance of Braintree Adapter.
     *
     * @param int $storeId if null is provided as an argument, then current scope will be resolved
     * by \Magento\Framework\App\Config\ScopeCodeResolver (useful for most cases) but for adminhtml area the store
     * should be provided as the argument for correct config settings loading.
     * @return BraintreeAdapter
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            BraintreeAdapter::class,
            [
                'merchantId' => $this->config->getMerchantId($storeId),
                'publicKey' => $this->config->getValue(Config::KEY_PUBLIC_KEY, $storeId),
                'privateKey' => $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId),
                'environment' => $this->config->getEnvironment($storeId)
            ]
        );
    }
}
