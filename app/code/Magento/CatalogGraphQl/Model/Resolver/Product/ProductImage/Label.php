<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductImage;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns product's image label
 */
class Label implements ResolverInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @param ProductResourceModel $productResource
     */
    public function __construct(
        ProductResourceModel $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['image_type'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $imageType = $value['image_type'];
        $imagePath = $product->getData($imageType);
        $productId = (int)$product->getEntityId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        // null if image is not set
        if (null === $imagePath) {
            return $this->getAttributeValue($productId, 'name', $storeId);
        }

        $imageLabel = $this->getAttributeValue($productId, $imageType . '_label', $storeId);
        if (null === $imageLabel) {
            $imageLabel = $this->getAttributeValue($productId, 'name', $storeId);
        }

        return $imageLabel;
    }

    /**
     * Get attribute value
     *
     * @param int $productId
     * @param string $attributeCode
     * @param int $storeId
     * @return null|string Null if attribute value is not exists
     */
    private function getAttributeValue(int $productId, string $attributeCode, int $storeId): ?string
    {
        $value = $this->productResource->getAttributeRawValue($productId, $attributeCode, $storeId);
        return is_array($value) && empty($value) ? null : $value;
    }
}
