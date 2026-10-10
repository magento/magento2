<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductImageCache;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Returns media url
 */
class Url implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * @param ProductImageCache $productImageCache
     */
    public function __construct(
        private readonly ProductImageCache $productImageCache
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['image_type']) && !isset($value['file'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        if (isset($value['image_type'])) {
            $imagePath = $product->getData($value['image_type']);
            return $this->productImageCache->getUrl($value['image_type'], $imagePath);
        }
        if (isset($value['file'])) {
            return $this->productImageCache->getUrl('image', $value['file']);
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->productImageCache->_resetState();
    }
}
