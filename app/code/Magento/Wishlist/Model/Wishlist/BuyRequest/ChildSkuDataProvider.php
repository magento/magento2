<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;

/**
 * Build a configurable super_attribute buy request from the child SKU
 * supplied as `sku` in the addProductsToWishlist mutation when the
 * parent SKU is provided via `parent_sku`.
 *
 * Without this provider, wishlist items added through GraphQL using
 * parent_sku + sku do not resolve `configured_variant` because no
 * `simple_product` option is recorded against the wishlist item.
 */
class ChildSkuDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Configurable
     */
    private Configurable $configurableType;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurableType
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType
    ) {
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
    }

    /**
     * @inheritdoc
     */
    public function execute(WishlistItem $wishlistItemData, ?int $productId): array
    {
        $parentSku = $wishlistItemData->getParentSku();
        $sku = $wishlistItemData->getSku();

        if ($parentSku === null || $sku === null || $parentSku === $sku) {
            return [];
        }

        // If the caller already provided encoded selected_options the
        // dedicated SuperAttributeDataProvider takes care of building
        // the super_attribute map. Avoid duplicating its work.
        if (!empty($wishlistItemData->getSelectedOptions())) {
            return [];
        }

        try {
            $child = $this->productRepository->get($sku, false, null, true);
            $parent = $this->productRepository->get($parentSku, false, null, true);
        } catch (NoSuchEntityException $e) {
            return [];
        }

        if ($parent->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }

        $superAttribute = [];
        foreach ($this->configurableType->getConfigurableAttributesAsArray($parent) as $attribute) {
            $attributeId = (int)$attribute['attribute_id'];
            $attributeCode = $attribute['attribute_code'] ?? null;
            if (!$attributeId || !$attributeCode) {
                continue;
            }
            $value = $child->getData($attributeCode);
            if ($value !== null && $value !== '') {
                $superAttribute[$attributeId] = $value;
            }
        }

        if (empty($superAttribute)) {
            return [];
        }

        $result = ['super_attribute' => $superAttribute];
        if ($productId) {
            $result['product'] = $productId;
        }

        return $result;
    }
}
