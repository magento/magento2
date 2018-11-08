<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductImage;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns product's image url
 */
class Url implements ResolverInterface
{
    /**
     * @var ImageFactory
     */
    private $productImageFactory;

    /**
     * @param ImageFactory $productImageFactory
     */
    public function __construct(
        ImageFactory $productImageFactory
    ) {
        $this->productImageFactory = $productImageFactory;
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
        $imagePath = $product->getData($value['image_type']);

        $imageUrl = $this->getImageUrl($value['image_type'], $imagePath);
        return $imageUrl;
    }

    /**
     * Get image url
     *
     * @param string $imageType
     * @param string|null $imagePath Null if image is not set
     * @return string
     */
    private function getImageUrl(string $imageType, ?string $imagePath): string
    {
        $image = $this->productImageFactory->create();
        $image->setDestinationSubdir($imageType)
            ->setBaseFile($imagePath);
        $imageUrl = $image->getUrl();
        return $imageUrl;
    }
}
