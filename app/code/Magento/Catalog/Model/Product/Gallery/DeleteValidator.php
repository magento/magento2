<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;

/**
 * Validates media image for removal
 */
class DeleteValidator
{
    /**
     * @var Gallery
     */
    private Gallery $resourceModel;

    /**
     * @var ProductInterface|null
     */
    private ?ProductInterface $product = null;

    /**
     * @var array|null
     */
    private ?array $imagesWithRolesInOtherStoresCache = null;

    /**
     * @param Gallery $resourceModel
     */
    public function __construct(
        Gallery $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Validates media image for removal
     *
     * @param ProductInterface $product
     * @param string $imageFile
     * @return Phrase[]
     */
    public function validate(ProductInterface $product, string $imageFile): array
    {
        $errors = [];
        if (count($product->getStoreIds()) > 1) {
            if (in_array($imageFile, $this->getImagesWithRolesInOtherStores($product))) {
                $errors[] = __('The image cannot be removed as it has been assigned to the other image role');
            }
        }

        return $errors;
    }

    /**
     * Returns all images that are assigned to a role in store views other than the current store view
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getImagesWithRolesInOtherStores(ProductInterface $product): array
    {
        if ($this->product !== $product || !$this->imagesWithRolesInOtherStoresCache) {
            $this->product = $product;
            $storeIds = array_diff(
                array_merge($product->getStoreIds(), [Store::DEFAULT_STORE_ID]),
                [$product->getStoreId()]
            );
            $this->imagesWithRolesInOtherStoresCache = array_column(
                $this->resourceModel->getProductImages($product, $storeIds),
                'filepath'
            );
        }
        return $this->imagesWithRolesInOtherStoresCache;
    }
}
