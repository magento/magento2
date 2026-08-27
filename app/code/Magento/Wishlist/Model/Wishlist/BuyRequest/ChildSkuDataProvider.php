<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
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
 *
 * The configurable axes are read from the parent product's
 * `configurable_product_options` extension attribute so the provider does
 * not introduce a hard dependency on the ConfigurableProduct module; when
 * that module is absent the extension attribute is empty and the provider
 * is a no-op.
 */
class ChildSkuDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * Product type code of configurable products.
     */
    private const CONFIGURABLE_TYPE = 'configurable';

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
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

        $products = $this->resolveConfigurablePair($parentSku, $sku);
        if ($products === null) {
            return [];
        }
        [$parent, $child] = $products;

        $superAttribute = $this->buildSuperAttribute($parent, $child);
        if (empty($superAttribute)) {
            return [];
        }

        $result = ['super_attribute' => $superAttribute];
        if ($productId) {
            $result['product'] = $productId;
        }

        return $result;
    }

    /**
     * Load the parent/child pair and validate the parent is a configurable product.
     *
     * @param string $parentSku
     * @param string $sku
     * @return array{0: ProductInterface, 1: Product}|null
     */
    private function resolveConfigurablePair(string $parentSku, string $sku): ?array
    {
        try {
            $child = $this->productRepository->get($sku, false, null, true);
            $parent = $this->productRepository->get($parentSku, false, null, true);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        if ($parent->getTypeId() !== self::CONFIGURABLE_TYPE || !$child instanceof Product) {
            return null;
        }

        return [$parent, $child];
    }

    /**
     * Map the child's values for the parent's configurable axes to a super_attribute array.
     *
     * @param ProductInterface $parent
     * @param Product $child
     * @return array
     */
    private function buildSuperAttribute(ProductInterface $parent, Product $child): array
    {
        $attributeIds = $this->getConfigurableAttributeIds($parent);
        if (empty($attributeIds)) {
            return [];
        }

        $superAttribute = [];
        foreach ($child->getAttributes() as $attribute) {
            $attributeId = (int)$attribute->getAttributeId();
            if (!in_array($attributeId, $attributeIds, true)) {
                continue;
            }
            $value = $child->getData($attribute->getAttributeCode());
            if ($value !== null && $value !== '') {
                $superAttribute[$attributeId] = $value;
            }
        }

        return $superAttribute;
    }

    /**
     * Collect the configurable axis attribute IDs from the parent's extension attributes.
     *
     * @param ProductInterface $parent
     * @return int[]
     */
    private function getConfigurableAttributeIds(ProductInterface $parent): array
    {
        $extensionAttributes = $parent->getExtensionAttributes();
        $options = $extensionAttributes ? $extensionAttributes->getConfigurableProductOptions() : null;
        if (empty($options)) {
            return [];
        }

        $attributeIds = [];
        foreach ($options as $option) {
            $attributeId = (int)$option->getAttributeId();
            if ($attributeId) {
                $attributeIds[] = $attributeId;
            }
        }

        return $attributeIds;
    }
}
