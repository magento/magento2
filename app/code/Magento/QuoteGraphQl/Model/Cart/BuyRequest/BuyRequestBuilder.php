<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

/**
 * Build buy request for adding products to cart
 */
class BuyRequestBuilder
{
    /**
     * @var BuyRequestDataProviderInterface[]
     */
    private $providers;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param array $providers
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $providers = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->providers = $providers;
    }

    /**
     * Build buy request for adding product to cart
     *
     * @param array $cartItemData
     * @return DataObject
     */
    public function build(array $cartItemData): DataObject
    {
        $requestData = [[]];
        foreach ($this->providers as $provider) {
            $requestData[] = $provider->execute($cartItemData);
        }

        return $this->dataObjectFactory->create(['data' => array_merge(...$requestData)]);
    }
}
