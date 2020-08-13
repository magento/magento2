<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\BuyRequest;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Quote\Model\Cart\Data\CartItem;

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
     * @see \Magento\Quote\Model\Quote::addProduct
     * @param CartItem $cartItem
     * @return DataObject
     */
    public function build(CartItem $cartItem): DataObject
    {
        $requestData = [
            ['qty' => $cartItem->getQuantity()]
        ];

        /** @var BuyRequestDataProviderInterface $provider */
        foreach ($this->providers as $provider) {
            $requestData[] = $provider->execute($cartItem);
        }

        return $this->dataObjectFactory->create(['data' => array_merge(...$requestData)]);
    }
}
