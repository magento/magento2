<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

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

    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $providers = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->providers = $providers;
    }

    public function build(array $cartItemData): DataObject
    {
        $requestData = [];
        foreach ($this->providers as $provider) {
            $requestData = array_merge($requestData, $provider->execute($cartItemData));
        }

        return $this->dataObjectFactory->create(['data' => $requestData]);
    }
}
