<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;

/**
 * Building buy request for all product types
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
     * Build product buy request for adding to wishlist
     *
     * @param WishlistItem $wishlistItemData
     * @param int|null $productId
     *
     * @return DataObject
     */
    public function build(WishlistItem $wishlistItemData, ?int $productId = null): DataObject
    {
        $requestData = [
            [
                'qty' => $wishlistItemData->getQuantity(),
            ]
        ];

        foreach ($this->providers as $provider) {
            $requestData[] = $provider->execute($wishlistItemData, $productId);
        }

        return $this->dataObjectFactory->create(['data' => array_merge(...$requestData)]);
    }
}
